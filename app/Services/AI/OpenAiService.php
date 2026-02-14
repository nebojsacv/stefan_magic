<?php

namespace App\Services\AI;

use OpenAI;
use App\Models\ApiUsage;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    protected $client;
    protected $model;
    protected $visionModel;
    protected $temperature;
    protected $maxTokens;

    public function __construct()
    {
        $this->client = OpenAI::client(config('services.openai.api_key'));
        $this->model = config('services.openai.model', 'gpt-4-turbo-preview');
        $this->visionModel = config('services.openai.vision_model', 'gpt-4-vision-preview');
        $this->temperature = config('services.openai.temperature', 0.3);
        $this->maxTokens = config('services.openai.max_tokens', 4096);
    }

    /**
     * Analyze text using GPT-4
     */
    public function analyzeText(string $prompt, array $context = [], int $userId = null): array
    {
        try {
            $messages = [
                ['role' => 'system', 'content' => 'You are an expert in cybersecurity and NIS2 compliance, analyzing vendor risk assessments.'],
            ];

            foreach ($context as $item) {
                $messages[] = [
                    'role' => $item['role'] ?? 'user',
                    'content' => $item['content'],
                ];
            }

            $messages[] = ['role' => 'user', 'content' => $prompt];

            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $this->temperature,
                'max_tokens' => $this->maxTokens,
            ]);

            // Track usage
            if ($userId) {
                $this->trackUsage($userId, $response);
            }

            return [
                'success' => true,
                'content' => $response->choices[0]->message->content,
                'usage' => [
                    'prompt_tokens' => $response->usage->promptTokens,
                    'completion_tokens' => $response->usage->completionTokens,
                    'total_tokens' => $response->usage->totalTokens,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Analysis Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Analyze image/document using GPT-4 Vision
     */
    public function analyzeImage(string $imageUrl, string $prompt, int $userId = null): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => $this->visionModel,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt],
                            ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]],
                        ],
                    ],
                ],
                'max_tokens' => $this->maxTokens,
            ]);

            // Track usage
            if ($userId) {
                $this->trackUsage($userId, $response, 'vision');
            }

            return [
                'success' => true,
                'content' => $response->choices[0]->message->content,
                'usage' => [
                    'prompt_tokens' => $response->usage->promptTokens,
                    'completion_tokens' => $response->usage->completionTokens,
                    'total_tokens' => $response->usage->totalTokens,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Vision Analysis Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate structured JSON response
     */
    public function generateStructuredResponse(string $prompt, array $schema): array
    {
        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a precise data extraction assistant. Always respond with valid JSON.'],
                    ['role' => 'user', 'content' => $prompt . "\n\nRequired JSON schema: " . json_encode($schema)],
                ],
                'temperature' => 0.1, // Lower temperature for more precise extraction
                'max_tokens' => $this->maxTokens,
            ]);

            $content = $response->choices[0]->message->content;
            
            // Extract JSON from markdown code blocks if present
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $content = $matches[1];
            }

            $decoded = json_decode($content, true);

            return [
                'success' => true,
                'data' => $decoded,
                'raw' => $content,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Structured Response Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track API usage and costs
     */
    protected function trackUsage(int $userId, $response, string $endpoint = 'chat'): void
    {
        $cost = $this->calculateCost(
            $response->usage->promptTokens,
            $response->usage->completionTokens,
            $response->model
        );

        ApiUsage::create([
            'user_id' => $userId,
            'model' => $response->model,
            'prompt_tokens' => $response->usage->promptTokens,
            'completion_tokens' => $response->usage->completionTokens,
            'total_tokens' => $response->usage->totalTokens,
            'cost_usd' => $cost,
            'endpoint' => $endpoint,
        ]);
    }

    /**
     * Calculate cost based on model pricing
     */
    protected function calculateCost(int $promptTokens, int $completionTokens, string $model): float
    {
        // Pricing as of 2026 (approximate)
        $pricing = [
            'gpt-4-turbo-preview' => ['prompt' => 0.01, 'completion' => 0.03],
            'gpt-4-vision-preview' => ['prompt' => 0.01, 'completion' => 0.03],
            'gpt-4' => ['prompt' => 0.03, 'completion' => 0.06],
            'gpt-3.5-turbo' => ['prompt' => 0.0005, 'completion' => 0.0015],
        ];

        $rates = $pricing[$model] ?? ['prompt' => 0.01, 'completion' => 0.03];

        $promptCost = ($promptTokens / 1000) * $rates['prompt'];
        $completionCost = ($completionTokens / 1000) * $rates['completion'];

        return round($promptCost + $completionCost, 4);
    }

    /**
     * Check if user has reached their AI usage limit
     */
    public function checkUsageLimit(int $userId): array
    {
        $user = \App\Models\User::with('package')->find($userId);
        
        if (!$user || !$user->package) {
            return ['allowed' => false, 'reason' => 'No active package'];
        }

        // Get current month usage
        $monthlyUsage = ApiUsage::where('user_id', $userId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('cost_usd');

        $limit = $user->package->features['ai_cost_limit'] ?? 50;

        return [
            'allowed' => $monthlyUsage < $limit,
            'used' => $monthlyUsage,
            'limit' => $limit,
            'remaining' => max(0, $limit - $monthlyUsage),
        ];
    }
}

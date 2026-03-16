<?php

namespace App\Services;

use App\Ai\Agents\VendorRiskAnalysisAgent;
use App\Models\AiAnalysis;
use App\Models\Questionnaire;
use App\Models\Vendor;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

class AiAnalysisService
{
    public function analyze(Questionnaire $questionnaire): AiAnalysis
    {
        $questionnaire->load(['answers.question.options', 'vendor', 'template']);

        $startedAt = microtime(true);

        try {
            return $this->analyzeWithAi($questionnaire, $startedAt);
        } catch (Throwable $e) {
            Log::warning('[AI Analysis] AI call failed, falling back to score-based analysis.', [
                'questionnaire_id' => $questionnaire->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->analyzeWithScoring($questionnaire, $startedAt);
        }
    }

    protected function analyzeWithAi(Questionnaire $questionnaire, float $startedAt): AiAnalysis
    {
        $prompt = $this->buildPrompt($questionnaire);

        Log::info('[AI Analysis] Sending prompt to AI.', [
            'questionnaire_id' => $questionnaire->id,
            'vendor' => $questionnaire->vendor->name,
            'tier' => $questionnaire->template->risk_level,
            'answer_count' => $questionnaire->answers->count(),
        ]);

        $response = VendorRiskAnalysisAgent::make()->prompt($prompt);

        $elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);

        // HasStructuredOutput returns a StructuredAgentResponse with data in ->structured
        $result = $response instanceof StructuredAgentResponse
            ? $response->structured
            : json_decode($response->text, true);

        Log::info('[AI Analysis] Raw response received.', [
            'questionnaire_id' => $questionnaire->id,
            'elapsed_ms' => $elapsedMs,
            'tokens_used' => $response->usage?->totalTokens ?? null,
            'response_class' => get_class($response),
            'raw_text' => $response->text,
            'structured' => $response instanceof StructuredAgentResponse ? $response->structured : null,
        ]);

        if (empty($result)) {
            Log::warning('[AI Analysis] Empty result from AI response — falling back to scoring.', [
                'questionnaire_id' => $questionnaire->id,
                'raw_text' => $response->text,
            ]);
            throw new \RuntimeException('AI returned an empty structured result.');
        }

        Log::info('[AI Analysis] Parsed result.', [
            'questionnaire_id' => $questionnaire->id,
            'risk_level' => $result['risk_level'] ?? null,
            'total_risk_score' => $result['total_risk_score'] ?? null,
            'confidence_score' => $result['confidence_score'] ?? null,
            'key_findings' => $result['key_findings'] ?? [],
        ]);

        $analysis = AiAnalysis::create([
            'questionnaire_id' => $questionnaire->id,
            'model_used' => 'laravel-ai',
            'total_risk_score' => (int) ($result['total_risk_score'] ?? 50),
            'risk_level' => $result['risk_level'] ?? 'medium',
            'confidence_score' => (float) ($result['confidence_score'] ?? 0.8),
            'analysis_summary' => $result['analysis_summary'] ?? '',
            'key_findings' => $result['key_findings'] ?? [],
            'processed_at' => now(),
            'processing_time_ms' => $elapsedMs,
            'tokens_used' => $response->usage?->totalTokens ?? null,
        ]);

        Log::info('[AI Analysis] Analysis stored.', [
            'questionnaire_id' => $questionnaire->id,
            'ai_analysis_id' => $analysis->id,
            'risk_level' => $analysis->risk_level,
            'total_risk_score' => $analysis->total_risk_score,
        ]);

        $this->updateVendor($questionnaire->vendor, $result['risk_level'] ?? 'medium');

        $questionnaire->update([
            'status' => 'completed',
            'processing_status' => 'completed',
        ]);

        return $analysis;
    }

    protected function analyzeWithScoring(Questionnaire $questionnaire, float $startedAt): AiAnalysis
    {
        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($questionnaire->answers as $answer) {
            $question = $answer->question;
            $weight = (float) $question->scoring_weight;
            $score = $this->scoreForAnswer($answer);

            if ($score !== null) {
                $totalWeightedScore += $score * $weight;
                $totalWeight += $weight;
            }
        }

        $totalRiskScore = $totalWeight > 0
            ? (int) round($totalWeightedScore / $totalWeight)
            : 50;

        $riskLevel = match (true) {
            $totalRiskScore <= 33 => 'low',
            $totalRiskScore <= 66 => 'medium',
            default => 'high',
        };

        $elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);

        $analysis = AiAnalysis::create([
            'questionnaire_id' => $questionnaire->id,
            'model_used' => 'score-based',
            'total_risk_score' => $totalRiskScore,
            'risk_level' => $riskLevel,
            'confidence_score' => 0.75,
            'analysis_summary' => 'Risk assessment computed from weighted questionnaire scores. Configure an AI provider key for a detailed analysis.',
            'key_findings' => ['Score-based analysis completed — AI analysis unavailable or failed.'],
            'processed_at' => now(),
            'processing_time_ms' => $elapsedMs,
        ]);

        $this->updateVendor($questionnaire->vendor, $riskLevel);

        $questionnaire->update([
            'status' => 'completed',
            'processing_status' => 'completed',
        ]);

        return $analysis;
    }

    protected function buildPrompt(Questionnaire $questionnaire): string
    {
        $tier = strtoupper($questionnaire->template->risk_level ?? 'unknown');
        $vendorName = $questionnaire->vendor->name;
        $lines = ["Vendor: {$vendorName}", "Questionnaire Tier: {$tier}", '', 'Responses:'];

        foreach ($questionnaire->answers as $index => $answer) {
            $question = $answer->question;
            $response = is_array($answer->selected_options)
                ? implode(', ', $answer->selected_options)
                : ($answer->answer_text ?? 'No answer');

            $lines[] = sprintf(
                '%d. [%s | weight:%s] %s → %s',
                $index + 1,
                $question->question_category,
                $question->scoring_weight,
                $question->question_text,
                $response
            );
        }

        return implode("\n", $lines);
    }

    protected function scoreForAnswer($answer): ?int
    {
        if (is_array($answer->selected_options) && ! empty($answer->selected_options)) {
            $options = $answer->question->options;
            $scores = [];

            foreach ($answer->selected_options as $selectedText) {
                $option = $options->firstWhere('option_text', $selectedText);
                if ($option) {
                    $scores[] = $option->risk_value;
                }
            }

            return empty($scores) ? null : (int) round(array_sum($scores) / count($scores));
        }

        return null;
    }

    protected function updateVendor(Vendor $vendor, string $riskLevel): void
    {
        $months = match ($riskLevel) {
            'high' => 3,
            'medium' => 6,
            default => 12,
        };

        $vendor->update([
            'current_risk_level' => $riskLevel,
            'classification_status' => 'pending_approval',
            'next_reassessment_date' => now()->addMonths($months),
        ]);
    }
}

<?php

namespace App\Services\AI;

use App\Models\Questionnaire;
use App\Models\QuestionnaireAnswer;
use App\Models\AiAnalysis;
use App\Models\AiAnswerAnalysis;
use App\Models\MitigationPlan;
use Illuminate\Support\Facades\Log;

class AiAnalysisService
{
    protected OpenAiService $openAiService;

    public function __construct(OpenAiService $openAiService)
    {
        $this->openAiService = $openAiService;
    }

    /**
     * Main analysis orchestrator
     */
    public function analyzeQuestionnaire(Questionnaire $questionnaire): array
    {
        $startTime = microtime(true);

        try {
            // Step 1: Analyze individual answers
            $answerAnalyses = $this->analyzeAnswers($questionnaire);

            // Step 2: Calculate overall risk
            $overallRisk = $this->calculateOverallRisk($answerAnalyses);

            // Step 3: Generate risk summary
            $summary = $this->generateRiskSummary($questionnaire, $answerAnalyses, $overallRisk);

            // Step 4: Store AI analysis
            $aiAnalysis = $this->storeAnalysis($questionnaire, $overallRisk, $summary, $startTime);

            // Step 5: Generate mitigation plans
            $mitigationPlans = $this->generateMitigationPlans($aiAnalysis, $answerAnalyses);

            return [
                'success' => true,
                'analysis' => $aiAnalysis,
                'mitigation_plans' => $mitigationPlans,
                'processing_time' => round((microtime(true) - $startTime) * 1000),
            ];
        } catch (\Exception $e) {
            Log::error('AI Analysis Failed: ' . $e->getMessage(), [
                'questionnaire_id' => $questionnaire->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Analyze individual answers
     */
    protected function analyzeAnswers(Questionnaire $questionnaire): array
    {
        $answers = $questionnaire->answers()->with('question')->get();
        $analyses = [];

        foreach ($answers as $answer) {
            $prompt = $this->buildAnswerAnalysisPrompt($answer);
            
            $result = $this->openAiService->analyzeText(
                $prompt,
                [],
                $questionnaire->user_id
            );

            if ($result['success']) {
                $analysis = $this->parseAnswerAnalysis($result['content']);
                
                $aiAnswerAnalysis = AiAnswerAnalysis::create([
                    'answer_id' => $answer->id,
                    'evidence_summary' => $analysis['evidence_summary'] ?? null,
                    'compliance_verdict' => $analysis['compliance_verdict'] ?? null,
                    'risk_indicators' => $analysis['risk_indicators'] ?? [],
                    'suggested_score' => $analysis['suggested_score'] ?? null,
                    'confidence' => $analysis['confidence'] ?? 0.5,
                    'reasoning' => $analysis['reasoning'] ?? null,
                ]);

                $analyses[] = [
                    'answer' => $answer,
                    'analysis' => $aiAnswerAnalysis,
                ];
            }
        }

        return $analyses;
    }

    /**
     * Build prompt for answer analysis
     */
    protected function buildAnswerAnalysisPrompt(QuestionnaireAnswer $answer): string
    {
        $question = $answer->question;
        
        $prompt = "Analyze this vendor security questionnaire answer:\n\n";
        $prompt .= "Question: {$question->question_text}\n";
        $prompt .= "Answer: {$answer->answer_text}\n";

        if ($answer->evidence_files) {
            $prompt .= "\nEvidence files provided: " . count($answer->evidence_files) . " file(s)\n";
        }

        $prompt .= "\nProvide analysis in this JSON format:\n";
        $prompt .= "{\n";
        $prompt .= "  \"evidence_summary\": \"Brief summary of evidence quality\",\n";
        $prompt .= "  \"compliance_verdict\": \"pass|fail|partial|insufficient_evidence\",\n";
        $prompt .= "  \"risk_indicators\": [\"list\", \"of\", \"specific\", \"risks\"],\n";
        $prompt .= "  \"suggested_score\": 0-100,\n";
        $prompt .= "  \"confidence\": 0.0-1.0,\n";
        $prompt .= "  \"reasoning\": \"Detailed explanation\"\n";
        $prompt .= "}";

        return $prompt;
    }

    /**
     * Parse AI answer analysis response
     */
    protected function parseAnswerAnalysis(string $content): array
    {
        // Try to extract JSON
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
            if ($decoded) {
                return $decoded;
            }
        }

        // Fallback parsing if JSON extraction fails
        return [
            'reasoning' => $content,
            'confidence' => 0.5,
        ];
    }

    /**
     * Calculate overall risk
     */
    protected function calculateOverallRisk(array $answerAnalyses): array
    {
        $scores = [];
        $riskIndicators = [];

        foreach ($answerAnalyses as $item) {
            if (isset($item['analysis']->suggested_score)) {
                $scores[] = $item['analysis']->suggested_score;
            }
            if ($item['analysis']->risk_indicators) {
                $riskIndicators = array_merge($riskIndicators, $item['analysis']->risk_indicators);
            }
        }

        $avgScore = count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
        
        // Determine risk level
        if ($avgScore >= 80) {
            $riskLevel = 'low';
        } elseif ($avgScore >= 60) {
            $riskLevel = 'medium';
        } else {
            $riskLevel = 'high';
        }

        return [
            'total_score' => round($avgScore),
            'risk_level' => $riskLevel,
            'risk_indicators' => array_unique($riskIndicators),
        ];
    }

    /**
     * Generate risk summary using AI
     */
    protected function generateRiskSummary(Questionnaire $questionnaire, array $answerAnalyses, array $overallRisk): string
    {
        $prompt = "Generate a concise executive summary for this vendor risk assessment:\n\n";
        $prompt .= "Vendor: {$questionnaire->vendor->name}\n";
        $prompt .= "Risk Level: {$overallRisk['risk_level']}\n";
        $prompt .= "Overall Score: {$overallRisk['total_score']}/100\n\n";
        $prompt .= "Key Risk Indicators:\n";
        
        foreach ($overallRisk['risk_indicators'] as $indicator) {
            $prompt .= "- {$indicator}\n";
        }

        $prompt .= "\nProvide a 2-3 paragraph summary suitable for executives.";

        $result = $this->openAiService->analyzeText($prompt, [], $questionnaire->user_id);

        return $result['success'] ? $result['content'] : 'Summary generation failed.';
    }

    /**
     * Store AI analysis
     */
    protected function storeAnalysis(Questionnaire $questionnaire, array $overallRisk, string $summary, float $startTime): AiAnalysis
    {
        $processingTime = round((microtime(true) - $startTime) * 1000);

        return AiAnalysis::create([
            'questionnaire_id' => $questionnaire->id,
            'model_used' => config('services.openai.model'),
            'total_risk_score' => $overallRisk['total_score'],
            'risk_level' => $overallRisk['risk_level'],
            'confidence_score' => 0.85, // Calculate based on individual confidences
            'analysis_summary' => $summary,
            'key_findings' => $overallRisk['risk_indicators'],
            'processed_at' => now(),
            'processing_time_ms' => $processingTime,
        ]);
    }

    /**
     * Generate mitigation plans
     */
    protected function generateMitigationPlans(AiAnalysis $aiAnalysis, array $answerAnalyses): array
    {
        $riskAreas = $this->identifyHighRiskAreas($answerAnalyses);
        $plans = [];

        foreach ($riskAreas as $riskArea) {
            $prompt = "Generate a mitigation plan for this security risk:\n\n";
            $prompt .= "Risk: {$riskArea['risk']}\n";
            $prompt .= "Context: {$riskArea['context']}\n\n";
            $prompt .= "Provide:\n";
            $prompt .= "1. Specific actionable recommendations\n";
            $prompt .= "2. Priority level (critical/high/medium/low)\n";
            $prompt .= "3. NIS2 compliance references if applicable\n";

            $result = $this->openAiService->analyzeText($prompt, []);

            if ($result['success']) {
                $plan = MitigationPlan::create([
                    'ai_analysis_id' => $aiAnalysis->id,
                    'risk_category' => $riskArea['category'],
                    'severity' => $this->determineSeverity($riskArea),
                    'recommendation' => $result['content'],
                    'action_items' => $this->extractActionItems($result['content']),
                    'priority' => $riskArea['priority'] ?? 1,
                ]);

                $plans[] = $plan;
            }
        }

        return $plans;
    }

    /**
     * Identify high-risk areas requiring mitigation
     */
    protected function identifyHighRiskAreas(array $answerAnalyses): array
    {
        $highRiskAreas = [];

        foreach ($answerAnalyses as $item) {
            $analysis = $item['analysis'];
            
            if ($analysis->compliance_verdict === 'fail' || 
                ($analysis->suggested_score && $analysis->suggested_score < 60)) {
                
                $highRiskAreas[] = [
                    'risk' => implode(', ', $analysis->risk_indicators ?? []),
                    'context' => $analysis->reasoning,
                    'category' => $item['answer']->question->question_category ?? 'General',
                    'priority' => $analysis->suggested_score < 40 ? 1 : 2,
                ];
            }
        }

        return $highRiskAreas;
    }

    /**
     * Determine severity based on risk area
     */
    protected function determineSeverity(array $riskArea): string
    {
        if ($riskArea['priority'] === 1) {
            return 'critical';
        }
        return 'high';
    }

    /**
     * Extract action items from recommendation text
     */
    protected function extractActionItems(string $text): array
    {
        // Simple extraction of numbered or bulleted lists
        preg_match_all('/(?:^|\n)\s*[\d\-\*]+[\.\)]\s*(.+?)(?=\n|$)/m', $text, $matches);
        
        return array_values(array_filter($matches[1] ?? []));
    }
}

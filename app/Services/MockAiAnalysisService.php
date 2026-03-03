<?php

namespace App\Services;

use App\Models\AiAnalysis;
use App\Models\Questionnaire;
use App\Models\Vendor;

class MockAiAnalysisService
{
    public function analyze(Questionnaire $questionnaire): AiAnalysis
    {
        $questionnaire->load(['answers.question.options', 'vendor']);

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($questionnaire->answers as $answer) {
            $question = $answer->question;
            $weight = (float) $question->scoring_weight;

            $score = $this->getScoreForAnswer($answer);

            if ($score !== null) {
                $totalWeightedScore += $score * $weight;
                $totalWeight += $weight;
            }
        }

        $totalRiskScore = $totalWeight > 0
            ? (int) round($totalWeightedScore / $totalWeight)
            : 50;

        $riskLevel = $this->scoreToRiskLevel($totalRiskScore);

        $analysis = AiAnalysis::create([
            'questionnaire_id' => $questionnaire->id,
            'model_used' => 'mock-simulator',
            'total_risk_score' => $totalRiskScore,
            'risk_level' => $riskLevel,
            'confidence_score' => 0.85,
            'analysis_summary' => 'Simulated risk analysis based on questionnaire responses. Replace with actual AI analysis in production.',
            'key_findings' => ['Automated mock analysis completed'],
            'processed_at' => now(),
            'processing_time_ms' => rand(100, 500),
        ]);

        $this->updateVendorFromAnalysis($questionnaire->vendor, $riskLevel);

        $questionnaire->update([
            'status' => 'completed',
            'processing_status' => 'completed',
        ]);

        return $analysis;
    }

    protected function getScoreForAnswer($answer): ?int
    {
        $question = $answer->question;

        if ($answer->selected_options !== null && is_array($answer->selected_options)) {
            $options = $question->options;
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

    protected function scoreToRiskLevel(int $score): string
    {
        return match (true) {
            $score <= 33 => 'low',
            $score <= 66 => 'medium',
            default => 'high',
        };
    }

    protected function updateVendorFromAnalysis(Vendor $vendor, string $riskLevel): void
    {
        $vendor->update([
            'current_risk_level' => $riskLevel,
            'classification_status' => 'pending_approval',
            'next_reassessment_date' => now()->addMonths($this->riskLevelToReassessmentMonths($riskLevel)),
        ]);
    }

    protected function riskLevelToReassessmentMonths(string $riskLevel): int
    {
        return match ($riskLevel) {
            'high' => 3,
            'medium' => 6,
            default => 12,
        };
    }
}

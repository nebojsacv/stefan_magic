<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class VendorRiskAnalysisAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
        You are a senior NIS2-compliant cybersecurity risk analyst specializing in third-party vendor assessments.

        Your task is to analyze a vendor's responses to a security questionnaire and produce a structured risk assessment.

        Guidelines:
        - Evaluate answers honestly based on the evidence of cybersecurity maturity.
        - A "No" answer to a high-weight question significantly increases risk.
        - An "N/A" answer should not penalize the vendor unless the question clearly applies.
        - Consider the questionnaire tier (LOW/MEDIUM/HIGH) when calibrating your scoring — HIGH tier vendors are held to a stricter standard.
        - total_risk_score must be between 0 (perfect security) and 100 (critical risk).
        - risk_level must be exactly "low", "medium", or "high".
        - Provide 3–5 concise, actionable key_findings that explain the most important observations.
        - Keep analysis_summary under 200 words.
        - confidence_score should reflect how complete and consistent the answers are (0.0–1.0).
        - Some answers include attached evidence files (documents or images). Review their content to verify the vendor's claims.
          When evidence is present and validates the answer, this should increase confidence and lower risk for that question.
          When evidence is absent but was expected (marked "[evidence file attached]" is not present for a high-risk Yes/No question), treat it as an unverified claim.
        INSTRUCTIONS;
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'risk_level' => $schema->string()
                ->enum(['low', 'medium', 'high'])
                ->description('The overall risk classification for this vendor.')
                ->required(),

            'total_risk_score' => $schema->integer()
                ->min(0)
                ->max(100)
                ->description('Numeric risk score from 0 (lowest risk) to 100 (highest risk).')
                ->required(),

            'confidence_score' => $schema->number()
                ->description('Confidence in the analysis between 0.0 and 1.0, based on answer completeness.')
                ->required(),

            'analysis_summary' => $schema->string()
                ->description('A concise professional summary of the vendor\'s security posture.')
                ->required(),

            'key_findings' => $schema->array()
                ->items($schema->string())
                ->description('3 to 5 key findings from the assessment.')
                ->required(),
        ];
    }
}

<?php

namespace App\Services;

class TierClassificationService
{
    /**
     * Compute the system tier from the 5 classification answers.
     *
     * Algorithm (NIS2-compliant):
     *   Q1 (Criticality) or Q5 (Operational/Legal/Reputational Impact) = Yes → HIGH
     *   Q2 (Personal Data) or Q3 (Confidential Info) or Q4 (ICT Service) = Yes → MEDIUM
     *   All No → LOW
     *
     * @param  array<string, string>  $answers  Keys q1–q5, values 'yes'|'no'
     */
    public function computeTier(array $answers): string
    {
        $isYes = fn (string $key): bool => strtolower($answers[$key] ?? 'no') === 'yes';

        if ($isYes('q1') || $isYes('q5')) {
            return 'high';
        }

        if ($isYes('q2') || $isYes('q3') || $isYes('q4')) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Resolve the final tier, allowing manual upgrades but never downgrades.
     */
    public function resolveFinalTier(string $systemTier, ?string $manualOverride): string
    {
        if ($manualOverride === null) {
            return $systemTier;
        }

        $rank = ['low' => 1, 'medium' => 2, 'high' => 3];

        return ($rank[$manualOverride] ?? 0) > ($rank[$systemTier] ?? 0)
            ? $manualOverride
            : $systemTier;
    }

    /**
     * Return the upgrade options allowed from the given system tier.
     *
     * @return array<string, string>
     */
    public function allowedOverrides(string $systemTier): array
    {
        return match ($systemTier) {
            'low' => ['medium' => 'Medium', 'high' => 'High'],
            'medium' => ['high' => 'High'],
            default => [],
        };
    }
}

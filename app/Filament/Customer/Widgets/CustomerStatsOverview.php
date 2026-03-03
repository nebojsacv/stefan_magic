<?php

namespace App\Filament\Customer\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CustomerStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user()->load('package');
        $vendors = $user->vendors();

        $totalVendors = $vendors->count();
        $highRisk = (clone $vendors)->where('current_risk_level', 'high')->count();
        $mediumRisk = (clone $vendors)->where('current_risk_level', 'medium')->count();
        $lowRisk = (clone $vendors)->where('current_risk_level', 'low')->count();

        $totalQuestionnaires = $user->questionnaires()->count();
        $submittedQuestionnaires = $user->questionnaires()->where('is_submitted', true)->count();

        $assessmentsAllowed = $user->assessments_allowed;
        $remaining = $assessmentsAllowed === -1
            ? '∞'
            : max(0, $assessmentsAllowed - $totalVendors);

        $assessmentDescription = $assessmentsAllowed === -1
            ? 'Unlimited plan'
            : "{$remaining} remaining of {$assessmentsAllowed}";

        $assessmentColor = 'success';
        if ($assessmentsAllowed !== -1) {
            $usedPercent = $assessmentsAllowed > 0 ? ($totalVendors / $assessmentsAllowed) * 100 : 0;
            if ($usedPercent >= 90) {
                $assessmentColor = 'danger';
            } elseif ($usedPercent >= 70) {
                $assessmentColor = 'warning';
            }
        }

        $riskDescription = "{$highRisk} high · {$mediumRisk} medium · {$lowRisk} low";

        return [
            Stat::make('Total Vendors', $totalVendors)
                ->description($assessmentDescription)
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color($assessmentColor),

            Stat::make('Risk Breakdown', $highRisk.' High Risk')
                ->description($riskDescription)
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($highRisk > 0 ? 'danger' : 'success'),

            Stat::make('Questionnaires', $totalQuestionnaires)
                ->description("{$submittedQuestionnaires} submitted")
                ->descriptionIcon('heroicon-m-document-check')
                ->color('primary'),

            Stat::make('Package', $user->package?->package_name ?? 'No Package')
                ->description($user->status === 'active' ? 'Active subscription' : ucfirst($user->status ?? 'trial'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->color($user->status === 'active' ? 'success' : 'warning'),
        ];
    }
}

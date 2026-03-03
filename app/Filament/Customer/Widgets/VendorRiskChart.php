<?php

namespace App\Filament\Customer\Widgets;

use App\Models\Vendor;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class VendorRiskChart extends ChartWidget
{
    protected ?string $heading = 'Vendor Risk Overview';

    protected ?string $description = 'Breakdown of your vendors by risk level';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $userId = Auth::id();

        $high = Vendor::where('user_id', $userId)->where('current_risk_level', 'high')->count();
        $medium = Vendor::where('user_id', $userId)->where('current_risk_level', 'medium')->count();
        $low = Vendor::where('user_id', $userId)->where('current_risk_level', 'low')->count();
        $unclassified = Vendor::where('user_id', $userId)->whereNull('current_risk_level')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Vendors',
                    'data' => [$high, $medium, $low, $unclassified],
                    'backgroundColor' => ['#ef4444', '#f59e0b', '#22c55e', '#9ca3af'],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['High Risk', 'Medium Risk', 'Low Risk', 'Unclassified'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '65%',
        ];
    }
}

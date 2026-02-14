<?php

namespace App\Filament\Widgets;

use App\Models\QuestionnaireTemplate;
use App\Models\User;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalClients = User::where('role', 'customer')->count();
        $activeClients = User::where('role', 'customer')->where('status', 'active')->count();
        $totalVendors = Vendor::count();
        $totalQuestionnaires = QuestionnaireTemplate::count();
        $activeQuestionnaires = QuestionnaireTemplate::where('is_active', true)->count();

        return [
            Stat::make('Total Clients', $totalClients)
                ->description($activeClients . ' active clients')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
            
            Stat::make('Total Vendors', $totalVendors)
                ->description('Across all clients')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary')
                ->chart([3, 5, 3, 7, 4, 5, 6, 3]),
            
            Stat::make('Questionnaire Templates', $totalQuestionnaires)
                ->description($activeQuestionnaires . ' active templates')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->chart([4, 5, 6, 7, 5, 4, 6, 5]),
        ];
    }
}

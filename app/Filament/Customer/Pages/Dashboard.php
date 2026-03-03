<?php

namespace App\Filament\Customer\Pages;

use App\Filament\Customer\Resources\Vendors\VendorResource;
use App\Filament\Customer\Widgets\CustomerStatsOverview;
use App\Filament\Customer\Widgets\RecentQuestionnaires;
use App\Filament\Customer\Widgets\RecentVendors;
use App\Filament\Customer\Widgets\VendorRiskChart;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $title = 'Dashboard';

    public function getColumns(): int|array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            CustomerStatsOverview::class,
            VendorRiskChart::class,
            RecentVendors::class,
            RecentQuestionnaires::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $canAdd = $user->canCreateVendor();

        return [
            Action::make('addVendor')
                ->label('Add Vendor')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->color('primary')
                ->url(VendorResource::getUrl('create'))
                ->disabled(! $canAdd)
                ->tooltip(! $canAdd ? 'You have reached your vendor assessment limit. Please upgrade your plan.' : null),

            Action::make('managePlan')
                ->label('Manage Plan')
                ->icon(Heroicon::OutlinedCreditCard)
                ->color('gray')
                ->url(Subscription::getUrl()),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [];
    }
}

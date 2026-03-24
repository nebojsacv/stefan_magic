<?php

namespace App\Filament\Customer\Resources\Vendors\Pages;

use App\Filament\Customer\Resources\Vendors\VendorResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $isAtLimit = $user->assessments_allowed !== -1
            && $user->vendors()->count() >= $user->assessments_allowed;

        if ($isAtLimit) {
            return [
                Action::make('limitReached')
                    ->label('Upgrade to Add More')
                    ->icon(Heroicon::OutlinedArrowUpCircle)
                    ->color('warning')
                    ->url(fn () => \App\Filament\Customer\Pages\Subscription::getUrl()),
            ];
        }

        return [
            CreateAction::make(),
        ];
    }
}

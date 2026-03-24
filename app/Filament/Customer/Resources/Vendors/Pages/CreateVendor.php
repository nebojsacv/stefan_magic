<?php

namespace App\Filament\Customer\Resources\Vendors\Pages;

use App\Filament\Customer\Resources\Vendors\VendorResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateVendor extends CreateRecord
{
    protected static string $resource = VendorResource::class;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->assessments_allowed !== -1) {
            $used = $user->vendors()->count();

            if ($used >= $user->assessments_allowed) {
                Notification::make()
                    ->title('Vendor limit reached')
                    ->body("Your plan allows {$user->assessments_allowed} vendor(s). Please upgrade to add more.")
                    ->warning()
                    ->send();

                $this->redirect(VendorResource::getUrl('index'));

                return;
            }
        }

        parent::mount();
    }

    protected function getRedirectUrl(): string
    {
        return VendorResource::getUrl('classify', ['record' => $this->getRecord()]);
    }
}

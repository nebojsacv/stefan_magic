<?php

namespace App\Filament\Customer\Pages;

use App\Models\Package;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\Facades\Auth;
use BackedEnum;

class Subscription extends Page
{

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Subscription';

    protected static ?string $title = 'Manage Subscription';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.customer.pages.subscription';

    public static function canAccess(): bool
    {
        // Hide subscription page from superadmin users
        return auth()->user()->role !== 'superadmin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from navigation for superadmin
        return auth()->user()->role !== 'superadmin';
    }

    public ?int $selectedPackageId = null;

    public function mount(): void
    {
        $this->selectedPackageId = Auth::user()->package_id;
    }

    public function selectPackage(int $packageId): void
    {
        $this->selectedPackageId = $packageId;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changePackage')
                ->label('Update Package')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Change Subscription Package')
                ->modalDescription(function () {
                    $user = Auth::user();
                    $currentPackage = $user->package;
                    $selectedPackageId = $this->selectedPackageId ?? $user->package_id;
                    $newPackage = Package::find($selectedPackageId);
                    
                    if ($newPackage && $currentPackage && $newPackage->id != $currentPackage->id) {
                        if ($newPackage->price > $currentPackage->price) {
                            return 'You are upgrading to ' . $newPackage->package_name . '. Your assessment limit will increase and new features will be available immediately.';
                        } else {
                            return 'You are downgrading to ' . $newPackage->package_name . '. Your assessment limit will be adjusted at the start of your next billing cycle.';
                        }
                    }
                    
                    return 'Please select a different package to make changes.';
                })
                ->modalSubmitActionLabel('Confirm Change')
                ->action(function () {
                    $user = Auth::user();
                    $selectedPackageId = $this->selectedPackageId ?? null;
                    
                    if (!$selectedPackageId) {
                        Notification::make()
                            ->title('Please select a package')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    if ($selectedPackageId == $user->package_id) {
                        Notification::make()
                            ->title('No changes made')
                            ->body('You are already on this package.')
                            ->warning()
                            ->send();
                        return;
                    }
                    
                    $newPackage = Package::find($selectedPackageId);
                    
                    if (!$newPackage) {
                        Notification::make()
                            ->title('Package not found')
                            ->danger()
                            ->send();
                        return;
                    }
                    
                    $user->package_id = $newPackage->id;
                    $user->assessments_allowed = $newPackage->assessments_allowed;
                    $user->status = $newPackage->price == 0 ? 'trial' : 'active';
                    $user->save();
                    
                    Notification::make()
                        ->title('Package updated successfully!')
                        ->body('Your subscription has been changed to ' . $newPackage->package_name . '.')
                        ->success()
                        ->send();
                    
                    $this->redirect(static::getUrl());
                }),
        ];
    }

    public function getCurrentPackageInfo(): array
    {
        $user = Auth::user();
        $package = $user->package;
        
        if (!$package) {
            return [
                'name' => 'No Package',
                'price' => 0,
                'assessments_used' => 0,
                'assessments_allowed' => 0,
                'features' => [],
            ];
        }
        
        $vendorCount = $user->vendors()->count();
        
        return [
            'name' => $package->package_name,
            'price' => $package->price,
            'billing_cycle' => $package->billing_cycle,
            'assessments_used' => $vendorCount,
            'assessments_allowed' => $package->assessments_allowed,
            'assessments_remaining' => $package->assessments_allowed == -1 ? 'Unlimited' : max(0, $package->assessments_allowed - $vendorCount),
            'features' => $package->features,
        ];
    }

    public function getPackages()
    {
        return Package::where('is_active', true)->orderBy('price')->get();
    }
}

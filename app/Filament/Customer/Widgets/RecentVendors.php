<?php

namespace App\Filament\Customer\Widgets;

use App\Filament\Customer\Resources\Vendors\VendorResource;
use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentVendors extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vendor::query()
                    ->where('user_id', Auth::id())
                    ->latest()
                    ->limit(5)
            )
            ->heading('Recent Vendors')
            ->description('Your latest added vendors')
            ->columns([
                TextColumn::make('name')
                    ->label('Vendor')
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('industry')
                    ->label('Industry')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('poc_name')
                    ->label('Contact Person'),

                TextColumn::make('current_risk_level')
                    ->label('Risk Level')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'Unclassified'),

                TextColumn::make('classification_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending_approval' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->since()
                    ->color('gray'),
            ])
            ->actions([
                Action::make('classify')
                    ->label('Classify')
                    ->icon('heroicon-m-shield-check')
                    ->color('warning')
                    ->url(fn (Vendor $record): string => VendorResource::getUrl('classify', ['record' => $record]))
                    ->visible(fn (Vendor $record): bool => $record->classification_status === 'pending'),

                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Vendor $record): string => VendorResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('No vendors yet')
            ->emptyStateDescription('Add your first vendor to get started.')
            ->emptyStateIcon('heroicon-o-building-office-2')
            ->paginated(false);
    }
}

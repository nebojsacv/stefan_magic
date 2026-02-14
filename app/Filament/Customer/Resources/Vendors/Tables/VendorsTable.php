<?php

namespace App\Filament\Customer\Resources\Vendors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Vendor Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('poc_name')
                    ->label('Contact Person')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('poc_email')
                    ->label('Contact Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                
                TextColumn::make('industry')
                    ->searchable()
                    ->toggleable(),
                
                TextColumn::make('current_risk_level')
                    ->label('Risk Level')
                    ->badge()
                    ->color(fn (string $state = null): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state = null): string => $state ? ucfirst($state) : 'Not Classified')
                    ->sortable(),
                
                TextColumn::make('classification_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending_approval' => 'warning',
                        'rejected' => 'danger',
                        'pending' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_approval' => 'Pending Approval',
                        default => ucwords(str_replace('_', ' ', $state)),
                    })
                    ->sortable(),
                
                TextColumn::make('next_reassessment_date')
                    ->label('Next Assessment')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('current_risk_level')
                    ->label('Risk Level')
                    ->options([
                        'high' => 'High Risk',
                        'medium' => 'Medium Risk',
                        'low' => 'Low Risk',
                    ]),
                
                SelectFilter::make('classification_status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                
                SelectFilter::make('is_active')
                    ->label('Active Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

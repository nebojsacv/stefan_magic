<?php

namespace App\Filament\Resources\ClassificationQuestions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClassificationQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('label')
                    ->label('Label')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Question Text')
                    ->limit(80)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('triggers_tier')
                    ->label('Triggers Tier')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('order_index')
                    ->label('Order')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('order_index')
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

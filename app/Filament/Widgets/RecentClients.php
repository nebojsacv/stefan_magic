<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentClients extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('role', 'customer')
                    ->with('package')
                    ->withCount('vendors')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                
                TextColumn::make('company_name')
                    ->label('Company')
                    ->searchable()
                    ->default('—'),
                
                TextColumn::make('package.package_name')
                    ->label('Package')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Free Trial' => 'gray',
                        'Basic' => 'info',
                        'Professional' => 'warning',
                        'Enterprise' => 'success',
                        default => 'gray',
                    }),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'trial' => 'warning',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('vendors_count')
                    ->label('Vendors')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
                
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->heading('Recent Clients')
            ->description('Latest 10 client registrations');
    }
}

<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Package Details')
                    ->schema([
                        TextInput::make('package_name')
                            ->label('Package Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Pro Plan, Enterprise'),
                        
                        TextInput::make('package_stripe_id')
                            ->label('Stripe Product ID')
                            ->maxLength(255)
                            ->placeholder('prod_xxxxx')
                            ->helperText('From Stripe dashboard'),
                        
                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->minValue(0),
                        
                        Select::make('billing_cycle')
                            ->required()
                            ->options([
                                'monthly' => 'Monthly',
                                'yearly' => 'Yearly',
                            ])
                            ->default('monthly'),
                        
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active packages are available for subscription'),
                    ])
                    ->columns(2),

                Section::make('Limits & Features')
                    ->schema([
                        TextInput::make('assessments_allowed')
                            ->label('Assessments Limit')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(10)
                            ->helperText('Number of vendor assessments per billing cycle'),
                        
                        KeyValue::make('features')
                            ->label('Additional Features')
                            ->keyLabel('Feature Name')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Feature')
                            ->columnSpanFull()
                            ->helperText('e.g., ai_cost_limit: 100, priority_support: true'),
                    ]),
            ]);
    }
}

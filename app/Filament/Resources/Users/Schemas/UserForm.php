<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        
                        TextInput::make('password')
                            ->password()
                            ->required(fn ($context) => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->revealable()
                            ->helperText('Leave blank to keep current password'),
                        
                        Select::make('role')
                            ->required()
                            ->options([
                                'super' => 'Super Admin',
                                'tester' => 'Customer/Tester',
                                'approver' => 'Approver',
                            ])
                            ->default('tester')
                            ->helperText('Super Admin: Full access | Tester: Vendor assessment | Approver: Review classifications'),
                        
                        Select::make('status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'trial' => 'Trial',
                            ])
                            ->default('trial'),
                    ])
                    ->columns(2),

                Section::make('Company Information')
                    ->schema([
                        TextInput::make('company_name')
                            ->maxLength(255),
                        
                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Select::make('timezone')
                            ->required()
                            ->searchable()
                            ->options(collect(timezone_identifiers_list())->mapWithKeys(fn ($tz) => [$tz => $tz]))
                            ->default('UTC'),
                    ])
                    ->columns(2),

                Section::make('Subscription & Limits')
                    ->schema([
                        Select::make('package_id')
                            ->label('Package')
                            ->relationship('package', 'package_name')
                            ->preload()
                            ->searchable()
                            ->helperText('Subscription package'),
                        
                        TextInput::make('assessments_allowed')
                            ->label('Assessments Limit')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Maximum number of vendor assessments allowed'),
                    ])
                    ->columns(2),
            ]);
    }
}

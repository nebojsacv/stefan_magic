<?php

namespace App\Filament\Customer\Resources\Vendors\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vendor Information')
                    ->description('Basic information about the vendor')
                    ->schema([
                        Hidden::make('user_id')
                            ->default(fn () => Auth::id()),
                        
                        TextInput::make('name')
                            ->label('Vendor Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Acme Cloud Services'),
                        
                        TextInput::make('industry')
                            ->maxLength(255)
                            ->placeholder('e.g., Cloud Computing, IT Services')
                            ->helperText('The vendor\'s primary industry'),
                        
                        Textarea::make('company_info')
                            ->label('Company Description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Brief description of the vendor and their services'),
                    ])
                    ->columns(2),

                Section::make('Point of Contact')
                    ->description('Primary contact person at the vendor')
                    ->schema([
                        TextInput::make('poc_name')
                            ->label('Contact Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('John Doe'),
                        
                        TextInput::make('poc_email')
                            ->label('Contact Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->placeholder('contact@vendor.com')
                            ->helperText('Questionnaire will be sent to this email'),
                    ])
                    ->columns(2),

                Section::make('Risk Classification')
                    ->description('Risk assessment information (auto-populated)')
                    ->schema([
                        Select::make('current_risk_level')
                            ->label('Current Risk Level')
                            ->options([
                                'low' => 'Low Risk',
                                'medium' => 'Medium Risk',
                                'high' => 'High Risk',
                            ])
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Will be determined through classification process'),
                        
                        Select::make('classification_status')
                            ->label('Classification Status')
                            ->options([
                                'pending' => 'Pending Classification',
                                'pending_approval' => 'Pending Approval',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->disabled()
                            ->dehydrated(),
                        
                        DatePicker::make('next_reassessment_date')
                            ->label('Next Assessment Date')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Automatic reassessment scheduling'),
                        
                        Toggle::make('is_active')
                            ->label('Active Vendor')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Inactive vendors will not receive questionnaires'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}

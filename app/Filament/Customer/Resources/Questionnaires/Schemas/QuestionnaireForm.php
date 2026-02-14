<?php

namespace App\Filament\Customer\Resources\Questionnaires\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuestionnaireForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('unique_id')
                    ->required(),
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->required(),
                Select::make('template_id')
                    ->relationship('template', 'name'),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                Toggle::make('is_opened')
                    ->required(),
                Toggle::make('is_submitted')
                    ->required(),
                TextInput::make('questions_completed')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('submitted_at'),
                TextInput::make('processing_status'),
            ]);
    }
}

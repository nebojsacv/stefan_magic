<?php

namespace App\Filament\Resources\ClassificationQuestions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClassificationQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Question')
                    ->schema([
                        TextInput::make('key')
                            ->label('Key (q1–q5)')
                            ->readOnly()
                            ->helperText('The key is fixed and used by the classification algorithm. Do not change it.')
                            ->required(),

                        TextInput::make('label')
                            ->label('Short Label')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Full Question Text (shown to user)')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Algorithm Configuration')
                    ->description('Controls which tier is triggered when the user answers Yes to this question. HIGH-triggering questions (q1, q5) take precedence over MEDIUM ones.')
                    ->schema([
                        Select::make('triggers_tier')
                            ->label('Triggers Tier on Yes')
                            ->options(['high' => 'High', 'medium' => 'Medium'])
                            ->required(),

                        TextInput::make('order_index')
                            ->label('Display Order')
                            ->required()
                            ->numeric()
                            ->minValue(1),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive questions are excluded from the classification flow.'),
                    ])
                    ->columns(3),
            ]);
    }
}

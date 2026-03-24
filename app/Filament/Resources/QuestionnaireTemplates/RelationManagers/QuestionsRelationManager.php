<?php

namespace App\Filament\Resources\QuestionnaireTemplates\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Question Details')
                    ->columns(2)
                    ->schema([
                        Textarea::make('question_text')
                            ->required()
                            ->columnSpanFull()
                            ->rows(3),

                        TextInput::make('question_category')
                            ->maxLength(255),

                        Select::make('type')
                            ->options([
                                'select_bool' => 'Yes / No',
                                'radio' => 'Radio (single choice)',
                                'checkbox' => 'Checkbox (multiple choice)',
                                'textarea' => 'Text area',
                                'text' => 'Text input',
                            ])
                            ->required(),

                        TextInput::make('order_index')
                            ->numeric()
                            ->default(0),

                        TextInput::make('scoring_weight')
                            ->numeric()
                            ->step(0.01)
                            ->default(1.00),
                    ]),

                Section::make('Evidence / File Attachment')
                    ->description('Control whether vendors are asked to upload supporting evidence for this question.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('need_evidence')
                            ->label('Show evidence upload field')
                            ->helperText('Must be enabled for evidence_required_when to take effect.')
                            ->reactive(),

                        Select::make('evidence_required_when')
                            ->label('Require evidence')
                            ->options([
                                'optional' => 'Optional — show upload but never enforce',
                                'if_yes' => 'If answer is "Yes" — required only when vendor answers affirmatively',
                                'always' => 'Always required — vendor must attach a file regardless of answer',
                            ])
                            ->placeholder('— disabled (no upload field) —')
                            ->helperText('Leave empty to hide the upload field entirely.'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text')
            ->defaultSort('order_index')
            ->columns([
                TextColumn::make('order_index')
                    ->label('#')
                    ->sortable()
                    ->width('50px'),

                TextColumn::make('question_text')
                    ->label('Question')
                    ->limit(80)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('question_category')
                    ->label('Category')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color('info'),

                TextColumn::make('scoring_weight')
                    ->label('Weight')
                    ->sortable(),

                TextColumn::make('evidence_required_when')
                    ->label('Evidence')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'always' => 'Always required',
                        'if_yes' => 'Required if Yes',
                        'optional' => 'Optional',
                        default => 'Disabled',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'always' => 'danger',
                        'if_yes' => 'warning',
                        'optional' => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

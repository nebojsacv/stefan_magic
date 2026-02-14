<?php

namespace App\Filament\Resources\QuestionnaireTemplates;

use App\Filament\Resources\QuestionnaireTemplates\Pages\CreateQuestionnaireTemplate;
use App\Filament\Resources\QuestionnaireTemplates\Pages\EditQuestionnaireTemplate;
use App\Filament\Resources\QuestionnaireTemplates\Pages\ListQuestionnaireTemplates;
use App\Filament\Resources\QuestionnaireTemplates\Schemas\QuestionnaireTemplateForm;
use App\Filament\Resources\QuestionnaireTemplates\Tables\QuestionnaireTemplatesTable;
use App\Models\QuestionnaireTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuestionnaireTemplateResource extends Resource
{
    protected static ?string $model = QuestionnaireTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return QuestionnaireTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionnaireTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestionnaireTemplates::route('/'),
            'create' => CreateQuestionnaireTemplate::route('/create'),
            'edit' => EditQuestionnaireTemplate::route('/{record}/edit'),
        ];
    }
}

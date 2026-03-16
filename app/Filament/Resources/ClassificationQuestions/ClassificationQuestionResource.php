<?php

namespace App\Filament\Resources\ClassificationQuestions;

use App\Filament\Resources\ClassificationQuestions\Pages\CreateClassificationQuestion;
use App\Filament\Resources\ClassificationQuestions\Pages\EditClassificationQuestion;
use App\Filament\Resources\ClassificationQuestions\Pages\ListClassificationQuestions;
use App\Filament\Resources\ClassificationQuestions\Schemas\ClassificationQuestionForm;
use App\Filament\Resources\ClassificationQuestions\Tables\ClassificationQuestionsTable;
use App\Models\ClassificationQuestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClassificationQuestionResource extends Resource
{
    protected static ?string $model = ClassificationQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Classification Questions';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ClassificationQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassificationQuestionsTable::configure($table);
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
            'index' => ListClassificationQuestions::route('/'),
            'create' => CreateClassificationQuestion::route('/create'),
            'edit' => EditClassificationQuestion::route('/{record}/edit'),
        ];
    }
}

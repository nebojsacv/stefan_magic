<?php

namespace App\Filament\Customer\Resources\Questionnaires;

use App\Filament\Customer\Resources\Questionnaires\Pages\CreateQuestionnaire;
use App\Filament\Customer\Resources\Questionnaires\Pages\EditQuestionnaire;
use App\Filament\Customer\Resources\Questionnaires\Pages\ListQuestionnaires;
use App\Filament\Customer\Resources\Questionnaires\Schemas\QuestionnaireForm;
use App\Filament\Customer\Resources\Questionnaires\Tables\QuestionnairesTable;
use App\Models\Questionnaire;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionnaireResource extends Resource
{
    protected static ?string $model = Questionnaire::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function canAccess(): bool
    {
        // Hide questionnaires from superadmin users (they manage templates in admin panel)
        return auth()->user()->role !== 'superadmin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Hide from navigation for superadmin
        return auth()->user()->role !== 'superadmin';
    }

    public static function form(Schema $schema): Schema
    {
        return QuestionnaireForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionnairesTable::configure($table);
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
            'index' => ListQuestionnaires::route('/'),
            'create' => CreateQuestionnaire::route('/create'),
            'edit' => EditQuestionnaire::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

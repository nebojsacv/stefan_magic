<?php

namespace App\Filament\Customer\Resources\Questionnaires\Pages;

use App\Filament\Customer\Resources\Questionnaires\QuestionnaireResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuestionnaires extends ListRecords
{
    protected static string $resource = QuestionnaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

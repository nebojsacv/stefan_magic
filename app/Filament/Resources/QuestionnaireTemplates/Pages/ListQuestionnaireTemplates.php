<?php

namespace App\Filament\Resources\QuestionnaireTemplates\Pages;

use App\Filament\Resources\QuestionnaireTemplates\QuestionnaireTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuestionnaireTemplates extends ListRecords
{
    protected static string $resource = QuestionnaireTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

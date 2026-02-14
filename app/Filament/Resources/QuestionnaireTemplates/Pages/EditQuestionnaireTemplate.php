<?php

namespace App\Filament\Resources\QuestionnaireTemplates\Pages;

use App\Filament\Resources\QuestionnaireTemplates\QuestionnaireTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestionnaireTemplate extends EditRecord
{
    protected static string $resource = QuestionnaireTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ClassificationQuestions\Pages;

use App\Filament\Resources\ClassificationQuestions\ClassificationQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClassificationQuestion extends EditRecord
{
    protected static string $resource = ClassificationQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

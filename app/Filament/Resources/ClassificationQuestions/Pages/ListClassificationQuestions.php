<?php

namespace App\Filament\Resources\ClassificationQuestions\Pages;

use App\Filament\Resources\ClassificationQuestions\ClassificationQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClassificationQuestions extends ListRecords
{
    protected static string $resource = ClassificationQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Customer\Resources\Questionnaires\Pages;

use App\Filament\Customer\Resources\Questionnaires\QuestionnaireResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestionnaire extends CreateRecord
{
    protected static string $resource = QuestionnaireResource::class;
}

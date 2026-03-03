<?php

use App\Livewire\PublicQuestionnaire;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/q/thank-you', 'questionnaire-thank-you')->name('questionnaire.thank-you');
Route::get('/q/{uniqueId}', PublicQuestionnaire::class)->name('questionnaire.public');

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireAnswer extends Model
{
    protected $fillable = [
        'questionnaire_id',
        'question_id',
        'answer_text',
        'selected_options',
        'evidence_files',
        'manual_score',
        'ai_score',
        'final_score',
        'risk_level',
    ];

    protected function casts(): array
    {
        return [
            'selected_options' => 'array',
            'evidence_files' => 'array',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}

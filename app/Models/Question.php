<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'template_id',
        'question_text',
        'question_category',
        'type',
        'need_evidence',
        'nis2_requirement_id',
        'order_index',
        'scoring_weight',
    ];

    protected function casts(): array
    {
        return [
            'need_evidence' => 'boolean',
            'scoring_weight' => 'decimal:2',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireTemplate::class, 'template_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class)->orderBy('order_index');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysis extends Model
{
    protected $fillable = [
        'questionnaire_id',
        'model_used',
        'total_risk_score',
        'risk_level',
        'confidence_score',
        'analysis_summary',
        'key_findings',
        'processed_at',
        'processing_time_ms',
        'tokens_used',
        'cost_usd',
    ];

    protected function casts(): array
    {
        return [
            'key_findings' => 'array',
            'confidence_score' => 'decimal:2',
            'cost_usd' => 'decimal:4',
            'processed_at' => 'datetime',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }
}

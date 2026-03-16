<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorClassification extends Model
{
    protected $fillable = [
        'vendor_id',
        'risk_level',
        'tier_system',
        'tier_manual_override',
        'tier_final',
        'classification_method',
        'criticality_score',
        'data_access_level',
        'dependency_level',
        'questionnaire_answers',
        'classification_answers',
        'classified_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'classification_answers' => 'array',
            'questionnaire_answers' => 'array',
            'approved_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorClassification extends Model
{
    protected $fillable = [
        'vendor_id',
        'risk_level',
        'classification_method',
        'criticality_score',
        'data_access_level',
        'dependency_level',
        'questionnaire_answers',
        'classified_by',
        'approved_by',
        'approved_at',
        'notes',
    ];
}

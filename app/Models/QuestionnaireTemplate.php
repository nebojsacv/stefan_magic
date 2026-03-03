<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireTemplate extends Model
{
    protected $fillable = [
        'name',
        'risk_level',
        'question_count',
        'is_nis2_compliant',
        'is_active',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_nis2_compliant' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'template_id')->orderBy('order_index');
    }
}

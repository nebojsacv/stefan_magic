<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Option extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'risk_value',
        'order_index',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassificationQuestion extends Model
{
    protected $fillable = [
        'key',
        'label',
        'description',
        'triggers_tier',
        'order_index',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order_index' => 'integer',
        ];
    }

    public function scopeActive($query): void
    {
        $query->where('is_active', true)->orderBy('order_index');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Questionnaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unique_id',
        'vendor_id',
        'template_id',
        'user_id',
        'status',
        'is_opened',
        'is_submitted',
        'questions_completed',
        'submitted_at',
        'processing_status',
    ];

    protected $casts = [
        'is_opened' => 'boolean',
        'is_submitted' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($questionnaire) {
            if (!$questionnaire->unique_id) {
                $questionnaire->unique_id = Str::uuid();
            }
        });
    }

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireTemplate::class, 'template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireAnswer::class);
    }

    public function aiAnalysis(): HasOne
    {
        return $this->hasOne(AiAnalysis::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('is_submitted', true);
    }

    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    // Helper methods
    public function getProgressPercentage(): float
    {
        $totalQuestions = $this->template->questions()->count();
        
        if ($totalQuestions === 0) {
            return 0;
        }

        return round(($this->questions_completed / $totalQuestions) * 100, 2);
    }

    public function getRiskLevel(): ?string
    {
        return $this->aiAnalysis?->risk_level;
    }
}

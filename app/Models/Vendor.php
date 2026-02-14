<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'poc_name',
        'poc_email',
        'company_info',
        'industry',
        'current_risk_level',
        'classification_method',
        'classification_status',
        'approved_by',
        'approved_at',
        'next_reassessment_date',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'approved_at' => 'datetime',
        'next_reassessment_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function classifications(): HasMany
    {
        return $this->hasMany(VendorClassification::class);
    }

    public function questionnaires(): HasMany
    {
        return $this->hasMany(Questionnaire::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(VendorIncident::class);
    }

    public function reassessments(): HasMany
    {
        return $this->hasMany(VendorReassessment::class);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(VendorDependency::class);
    }

    public function dependentVendors(): HasMany
    {
        return $this->hasMany(VendorDependency::class, 'depends_on_vendor_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRiskLevel($query, $level)
    {
        return $query->where('current_risk_level', $level);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('classification_status', 'pending_approval');
    }
}

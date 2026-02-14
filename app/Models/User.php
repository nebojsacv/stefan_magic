<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_name',
        'address',
        'package_id',
        'assessments_allowed',
        'status',
        'options',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'role' => 'tester',
        'status' => 'trial',
        'assessments_allowed' => 3,
        'timezone' => 'UTC',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'options' => 'array',
        ];
    }

    // Relationships
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function questionnaires(): HasMany
    {
        return $this->hasMany(Questionnaire::class);
    }

    public function apiUsage(): HasMany
    {
        return $this->hasMany(ApiUsage::class);
    }

    // Helper methods
    public function isSuper(): bool
    {
        return $this->role === 'super';
    }

    public function isTester(): bool
    {
        return $this->role === 'tester';
    }

    public function isApprover(): bool
    {
        return $this->role === 'approver';
    }

    public function canCreateVendor(): bool
    {
        if ($this->assessments_allowed === -1) {
            return true; // Unlimited
        }

        $usedAssessments = $this->vendors()->count();
        return $usedAssessments < $this->assessments_allowed;
    }

    public function getRemainingAssessments(): int
    {
        if ($this->assessments_allowed === -1) {
            return PHP_INT_MAX; // Unlimited
        }

        $usedAssessments = $this->vendors()->count();
        return max(0, $this->assessments_allowed - $usedAssessments);
    }
}

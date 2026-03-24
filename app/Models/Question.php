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
        'evidence_required_when',
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

    /**
     * Whether the evidence file upload should be displayed for this question.
     */
    public function hasEvidenceUpload(): bool
    {
        return $this->evidence_required_when !== null;
    }

    /**
     * Whether the evidence file is required given the vendor's answer for this question.
     */
    public function isEvidenceRequired(mixed $answer): bool
    {
        return match ($this->evidence_required_when) {
            'always' => true,
            'if_yes' => $this->answerIsAffirmative($answer),
            default => false,
        };
    }

    protected function answerIsAffirmative(mixed $answer): bool
    {
        $values = is_array($answer) ? $answer : [(string) $answer];

        foreach ($values as $value) {
            if (str_contains(strtolower((string) $value), 'yes')) {
                return true;
            }
        }

        return false;
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

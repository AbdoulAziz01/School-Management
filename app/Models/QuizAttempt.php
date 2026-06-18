<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'user_id',
        'school_id',
        'attempt_number',
        'score',
        'max_score',
        'answers',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'answers'        => 'array',
        'score'          => 'decimal:2',
        'max_score'      => 'decimal:2',
        'attempt_number' => 'integer',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function percentage(): float
    {
        $max = (float) $this->max_score;
        if ($max <= 0.0) {
            return 0.0;
        }

        return round(((float) $this->score / $max) * 100, 1);
    }

    public function gradeLetter(): string
    {
        return match (true) {
            $this->percentage() >= 90 => 'A',
            $this->percentage() >= 75 => 'B',
            $this->percentage() >= 60 => 'C',
            $this->percentage() >= 50 => 'D',
            default                   => 'F',
        };
    }

    public function gradeColor(): string
    {
        return match ($this->gradeLetter()) {
            'A'     => 'success',
            'B'     => 'info',
            'C'     => 'warning',
            'D'     => 'secondary',
            default => 'danger',
        };
    }
}

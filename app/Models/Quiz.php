<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'subject_id',
        'school_id',
        'teacher_id',
        'title',
        'description',
        'is_published',
        'time_limit',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'time_limit'   => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function attemptsFor(int $userId): HasMany
    {
        return $this->hasMany(QuizAttempt::class)->where('user_id', $userId);
    }

    public function totalPoints(): float
    {
        return (float) $this->questions()->sum('points');
    }
}

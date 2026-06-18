<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'school_id',
        'file_path',
        'submitted_at',
        'grade',
        'feedback',
        'status',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'grade'        => 'decimal:2',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded' && $this->grade !== null;
    }
}

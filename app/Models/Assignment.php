<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'due_date',
        'subject_id',
        'class_id',
        'teacher_id',
        'file_path',
        'points',
        'status',
        'school_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'points'   => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}

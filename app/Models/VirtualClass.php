<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VirtualClass extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'teacher_id',
        'class_id',
        'subject_id',
        'title',
        'description',
        'room_name',
        'meeting_password',
        'scheduled_at',
        'duration_minutes',
        'is_active',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'scheduled_at'     => 'datetime',
        'opened_at'        => 'datetime',
        'closed_at'        => 'datetime',
        'is_active'        => 'boolean',
        'duration_minutes' => 'integer',
    ];

    public static function generateRoomName(int $schoolId): string
    {
        return 'edumanager-' . $schoolId . '-' . Str::random(12);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function isUpcoming(): bool
    {
        return ! $this->is_active && $this->closed_at === null;
    }

    public function isPast(): bool
    {
        return $this->closed_at !== null && ! $this->is_active;
    }

    public function statusLabel(): string
    {
        if ($this->is_active) {
            return 'En cours';
        }
        if ($this->isPast()) {
            return 'Terminée';
        }

        return 'Planifiée';
    }

    public function statusColor(): string
    {
        if ($this->is_active) {
            return 'success';
        }
        if ($this->isPast()) {
            return 'secondary';
        }

        return 'warning';
    }
}

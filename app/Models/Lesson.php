<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Lesson extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'class_id',
        'subject_id',
        'school_id',
        'teacher_id',
        'title',
        'description',
        'file_type',
        'file_path',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

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

    /**
     * Retourne l'URL de téléchargement/visualisation selon le type de fichier.
     * - video/link → le file_path est déjà une URL externe
     * - pdf/doc    → URL signée via le disque privé (expire dans 60 min)
     */
    public function getFileUrlAttribute(): ?string
    {
        if (empty($this->file_path)) {
            return null;
        }

        if (in_array($this->file_type, ['video', 'link'], true)) {
            return $this->file_path;
        }

        return Storage::disk('local')->temporaryUrl($this->file_path, now()->addHour());
    }
}

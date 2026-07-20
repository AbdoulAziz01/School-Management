<x-mail::message>
# Correction de note par un enseignant

**{{ $teacher->name }}** vient de corriger une note déjà enregistrée. Chaque enseignant ne dispose que d'une seule correction par note — celle-ci vient d'être utilisée.

**Élève :** {{ $grade->user->name ?? '—' }} ({{ $grade->user->identifier ?? '—' }})
**Matière :** {{ $grade->subject->name ?? '—' }}
**Évaluation :** {{ \App\Support\Grading\GradeSequence::labels()[$grade->type] ?? $grade->type }} — Semestre {{ $grade->semester }}

**Ancienne note :** {{ $oldGrade }}/{{ rtrim(rtrim(number_format($maxGrade ?? 20, 2), '0'), '.') }}
**Nouvelle note :** {{ $newGrade }}/{{ rtrim(rtrim(number_format($maxGrade ?? 20, 2), '0'), '.') }}

**Date de la correction :** {{ $grade->teacher_edited_at?->format('d/m/Y à H:i') }}

Toute correction supplémentaire sur cette note devra désormais passer par l'administration.

<x-mail::button :url="route('admin.audit-log.index')">
Voir le journal d'audit
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>

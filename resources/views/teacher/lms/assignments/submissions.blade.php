@extends('teacher.layouts.app')

@section('title', 'Soumissions — ' . $assignment->title)

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('teacher.lms.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="h3 mb-0">Soumissions</h1>
        <p class="text-muted small mb-0">
            <strong>{{ $assignment->title }}</strong> ·
            {{ $assignment->subject->name ?? '' }} ·
            {{ $assignment->schoolClass->name ?? '' }}
        </p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 small alert-dismissible fade show">
        <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3 mb-4">
    @php
        $gradedCount = $submissions->where('status', 'graded')->count();
        $pendingCount = $submissions->where('status', 'pending')->count();
        $avgGrade = $submissions->where('grade', '!=', null)->avg('grade');
    @endphp
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-800 fs-3" style="color:#3b82f6;">{{ $submissions->count() }}</div>
            <div class="text-muted small">Soumissions totales</div>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-800 fs-3" style="color:#10b981;">{{ $gradedCount }}</div>
            <div class="text-muted small">Corrigées</div>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-800 fs-3" style="color:#f59e0b;">{{ $avgGrade ? number_format($avgGrade, 1) : '—' }}</div>
            <div class="text-muted small">Moyenne / {{ $assignment->points }}</div>
        </div>
    </div>
</div>

@if($submissions->isEmpty())
    <div class="card text-center py-5">
        <div class="card-body">
            <i class="fas fa-inbox fa-3x text-muted mb-3 opacity-50"></i>
            <p class="text-muted">Aucune soumission reçue pour ce devoir.</p>
        </div>
    </div>
@else
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th>Élève</th>
                        <th>Rendu le</th>
                        <th>Fichier</th>
                        <th>Note /{{ $assignment->points }}</th>
                        <th>Feedback</th>
                        <th>Statut</th>
                        <th class="text-end">Corriger</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($submissions as $sub)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:32px;height:32px;font-size:.75rem;">
                                    {{ strtoupper(substr($sub->student->name ?? '?', 0, 2)) }}
                                </div>
                                <span class="fw-semibold small">{{ $sub->student->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="small text-muted">
                            {{ \Carbon\Carbon::parse($sub->submitted_at)->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @if($sub->file_path)
                                <span class="badge bg-light text-dark border" title="{{ $sub->file_path }}">
                                    <i class="fas fa-file-pdf me-1 text-danger"></i>Déposé
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($sub->grade !== null)
                                <span class="badge {{ $sub->grade >= ($assignment->points * 0.5) ? 'bg-success' : 'bg-danger' }} rounded-pill">
                                    {{ number_format($sub->grade, 1) }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="small text-muted" style="max-width:180px;">
                            {{ Str::limit($sub->feedback, 60) ?? '—' }}
                        </td>
                        <td>
                            @if($sub->status === 'graded')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Corrigé</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">En attente</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#gradeModal{{ $sub->id }}">
                                <i class="fas fa-pen me-1"></i>Noter
                            </button>
                        </td>
                    </tr>

                    {{-- Modal notation ──────────────────────────────────── --}}
                    <div class="modal fade" id="gradeModal{{ $sub->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-pen me-2 text-primary"></i>
                                        Corriger — {{ $sub->student->name ?? '' }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('teacher.lms.submission.grade', $sub) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                Note <span class="text-danger">*</span>
                                                <small class="text-muted fw-normal">/ {{ $assignment->points }}</small>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" name="grade" class="form-control"
                                                       value="{{ $sub->grade }}"
                                                       min="0" max="{{ $assignment->points }}"
                                                       step="0.5" required>
                                                <span class="input-group-text">/ {{ $assignment->points }}</span>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label fw-semibold">Commentaire</label>
                                            <textarea name="feedback" class="form-control" rows="3"
                                                      placeholder="Retour pour l'élève…">{{ $sub->feedback }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i>Enregistrer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection

@extends('admin.layouts.app')

@section('title', 'Gestion E-Learning')

@push('styles')
<style>
    .lms-stat { border:none; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.07); }
    .action-btn { padding:3px 9px; font-size:.76rem; }
    .lms-tabs .nav-link { border-radius:8px 8px 0 0; font-weight:600; color:#64748b; }
    .lms-tabs .nav-link.active { color:#4f46e5; background:#fff; border-color:#e2e8f0 #e2e8f0 #fff; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-chalkboard-teacher me-2 text-indigo" style="color:#4f46e5;"></i>Gestion E-Learning</h1>
        <p class="text-muted mb-0">Vue globale de tous les contenus publiés dans l'établissement</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 small alert-dismissible fade show">
        <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Statistiques ─────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'Cours publiés',    'value'=>$stats['lessons'],     'icon'=>'fa-file-alt',        'color'=>'#3b82f6', 'bg'=>'#eff6ff'],
        ['label'=>'Devoirs créés',    'value'=>$stats['assignments'],  'icon'=>'fa-tasks',           'color'=>'#10b981', 'bg'=>'#f0fdf4'],
        ['label'=>'Soumissions',      'value'=>$stats['submissions'],  'icon'=>'fa-paper-plane',     'color'=>'#f59e0b', 'bg'=>'#fffbeb'],
        ['label'=>'Rendus corrigés',  'value'=>$stats['graded'],       'icon'=>'fa-check-circle',    'color'=>'#6366f1', 'bg'=>'#eef2ff'],
        ['label'=>'En attente',       'value'=>$stats['pending'],      'icon'=>'fa-clock',           'color'=>'#ef4444', 'bg'=>'#fff1f2'],
        ['label'=>'Quiz',             'value'=>$stats['quizzes'],      'icon'=>'fa-question-circle', 'color'=>'#8b5cf6', 'bg'=>'#f5f3ff'],
    ] as $s)
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card lms-stat">
            <div class="card-body py-3 px-3 d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:38px;height:38px;background:{{ $s['bg'] }};color:{{ $s['color'] }};">
                    <i class="fas {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="fw-800 fs-5 lh-1" style="font-weight:800;">{{ $s['value'] }}</div>
                    <div class="text-muted" style="font-size:.72rem;">{{ $s['label'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Onglets ─────────────────────────────────────────────────────────────── --}}
<ul class="nav nav-tabs lms-tabs mb-0 border-bottom" id="adminLmsTabs">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#al-lessons"><i class="fas fa-file-alt me-1"></i>Cours</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#al-assignments"><i class="fas fa-tasks me-1"></i>Devoirs</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#al-quizzes"><i class="fas fa-question-circle me-1"></i>Quiz</a></li>
</ul>

<div class="tab-content bg-white border border-top-0 p-3" style="border-radius:0 0 12px 12px;">

    {{-- COURS ──────────────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade show active" id="al-lessons">
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light text-muted">
                    <tr>
                        <th>#</th><th>Titre</th><th>Matière</th><th>Classe</th>
                        <th>Enseignant</th><th>Type</th><th>Publié</th><th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lessons as $lesson)
                    <tr>
                        <td class="text-muted">{{ $lesson->id }}</td>
                        <td class="fw-semibold">{{ Str::limit($lesson->title, 45) }}</td>
                        <td>{{ $lesson->subject->name ?? '—' }}</td>
                        <td>{{ $lesson->schoolClass->name ?? '—' }}</td>
                        <td>{{ $lesson->teacher->name ?? '—' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ strtoupper($lesson->file_type) }}</span></td>
                        <td class="text-muted">{{ $lesson->published_at?->format('d/m/Y') ?? '—' }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.lms.lesson.destroy', $lesson) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce cours ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger action-btn"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Aucun cours</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $lessons->links() }}</div>
    </div>

    {{-- DEVOIRS ──────────────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="al-assignments">
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light text-muted">
                    <tr>
                        <th>#</th><th>Titre</th><th>Matière</th><th>Classe</th>
                        <th>Enseignant</th><th>Soumissions</th><th>Statut</th><th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                    <tr>
                        <td class="text-muted">{{ $assignment->id }}</td>
                        <td class="fw-semibold">{{ Str::limit($assignment->title, 45) }}</td>
                        <td>{{ $assignment->subject->name ?? '—' }}</td>
                        <td>{{ $assignment->schoolClass->name ?? '—' }}</td>
                        <td>{{ $assignment->teacher->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                {{ $assignment->submissions_count }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $assignment->status === 'graded' ? 'bg-success-subtle text-success' : 'bg-info-subtle text-info' }} border">
                                {{ ucfirst($assignment->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <form action="{{ route('admin.lms.assignment.destroy', $assignment) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce devoir et ses soumissions ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger action-btn"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Aucun devoir</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $assignments->links() }}</div>
    </div>

    {{-- QUIZ ────────────────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="al-quizzes">
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light text-muted">
                    <tr>
                        <th>#</th><th>Titre</th><th>Matière</th><th>Enseignant</th>
                        <th>Questions</th><th>Durée</th><th>Publié</th><th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quizzes as $quiz)
                    <tr>
                        <td class="text-muted">{{ $quiz->id }}</td>
                        <td class="fw-semibold">{{ Str::limit($quiz->title, 45) }}</td>
                        <td>{{ $quiz->subject->name ?? '—' }}</td>
                        <td>{{ $quiz->teacher->name ?? '—' }}</td>
                        <td><span class="badge" style="background:#f5f3ff;color:#7c3aed;">{{ $quiz->questions_count }} Q</span></td>
                        <td class="text-muted">{{ $quiz->time_limit ? $quiz->time_limit . ' min' : '∞' }}</td>
                        <td>
                            <span class="badge {{ $quiz->is_published ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} border">
                                {{ $quiz->is_published ? 'Publié' : 'Brouillon' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <form action="{{ route('admin.lms.quiz.destroy', $quiz) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce quiz ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger action-btn"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Aucun quiz</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $quizzes->links() }}</div>
    </div>

</div>

@endsection

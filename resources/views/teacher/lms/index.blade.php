@extends('teacher.layouts.app')

@section('title', 'E-Learning — Mes contenus')

@push('styles')
<style>
    .lms-stat-card { border:none; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,0.07); }
    .content-table td { vertical-align: middle; }
    .lms-tabs .nav-link { border-radius:10px 10px 0 0; font-weight:600; color:#64748b; }
    .lms-tabs .nav-link.active { color:#0ea5e9; background:#fff; border-color:#e2e8f0 #e2e8f0 #fff; }
    .action-btn { padding:4px 10px; font-size:.78rem; border-radius:6px; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-chalkboard me-2 text-primary"></i>Espace E-Learning</h1>
        <p class="text-muted mb-0">Gérez vos cours, devoirs et quiz</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('teacher.lms.lesson.create') }}" class="btn btn-outline-primary">
            <i class="fas fa-plus me-1"></i>Nouveau cours
        </a>
        <a href="{{ route('teacher.lms.assignment.create') }}" class="btn btn-outline-success">
            <i class="fas fa-plus me-1"></i>Nouveau devoir
        </a>
        <a href="{{ route('teacher.lms.quiz.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Nouveau quiz
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 small">
        <i class="fas fa-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Stats ──────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @foreach([
        ['icon'=>'fa-file-alt',       'color'=>'#0ea5e9', 'bg'=>'#eff6ff', 'label'=>'Cours publiés',    'value'=>$lessonsCount],
        ['icon'=>'fa-tasks',          'color'=>'#10b981', 'bg'=>'#f0fdf4', 'label'=>'Devoirs créés',    'value'=>$assignmentsCount],
        ['icon'=>'fa-paper-plane',    'color'=>'#f59e0b', 'bg'=>'#fffbeb', 'label'=>'Soumissions',      'value'=>$submissionsCount],
        ['icon'=>'fa-question-circle','color'=>'#8b5cf6', 'bg'=>'#f5f3ff', 'label'=>'Quiz créés',       'value'=>$quizzesCount],
    ] as $s)
    <div class="col-6 col-md-3">
        <div class="card lms-stat-card">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:46px;height:46px;background:{{ $s['bg'] }};color:{{ $s['color'] }}">
                    <i class="fas {{ $s['icon'] }} fs-5"></i>
                </div>
                <div>
                    <div class="fw-800 fs-4 lh-1" style="color:#1e293b;font-weight:800;">{{ $s['value'] }}</div>
                    <div class="small text-muted mt-1">{{ $s['label'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Onglets ─────────────────────────────────────────────────────────────── --}}
<ul class="nav nav-tabs lms-tabs mb-0 border-bottom" id="teacherLmsTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tl-lessons">
            <i class="fas fa-file-alt me-1"></i>Cours ({{ $lessons->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tl-assignments">
            <i class="fas fa-tasks me-1"></i>Devoirs ({{ $assignments->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tl-quizzes">
            <i class="fas fa-question-circle me-1"></i>Quiz ({{ $quizzes->count() }})
        </a>
    </li>
</ul>

<div class="tab-content bg-white border border-top-0 p-3" style="border-radius:0 0 14px 14px;">

    {{-- COURS ─────────────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade show active" id="tl-lessons">
        @if($lessons->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3 opacity-50"></i>
                <p class="text-muted mb-3">Aucun cours publié pour l'instant.</p>
                <a href="{{ route('teacher.lms.lesson.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Publier un cours
                </a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle content-table mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th>Titre</th>
                        <th>Matière</th>
                        <th>Classe</th>
                        <th>Type</th>
                        <th>Publié le</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lessons as $lesson)
                    <tr>
                        <td class="fw-semibold">{{ $lesson->title }}</td>
                        <td><span class="badge bg-light text-dark">{{ $lesson->subject->name ?? '—' }}</span></td>
                        <td>{{ $lesson->schoolClass->name ?? '—' }}</td>
                        <td>
                            @php $typeColors = ['pdf'=>'danger','doc'=>'primary','video'=>'success','link'=>'info','other'=>'secondary'] @endphp
                            <span class="badge bg-{{ $typeColors[$lesson->file_type] ?? 'secondary' }}-subtle text-{{ $typeColors[$lesson->file_type] ?? 'secondary' }} border border-{{ $typeColors[$lesson->file_type] ?? 'secondary' }}-subtle">
                                {{ strtoupper($lesson->file_type) }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $lesson->published_at?->format('d/m/Y') ?? '—' }}</td>
                        <td class="text-end">
                            <form action="{{ route('teacher.lms.lesson.destroy', $lesson) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce cours ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger action-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- DEVOIRS ────────────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tl-assignments">
        @if($assignments->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-tasks fa-3x text-muted mb-3 opacity-50"></i>
                <p class="text-muted mb-3">Aucun devoir créé.</p>
                <a href="{{ route('teacher.lms.assignment.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Créer un devoir
                </a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle content-table mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th>Titre</th>
                        <th>Matière</th>
                        <th>Classe</th>
                        <th>Échéance</th>
                        <th>Soumissions</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                    <tr>
                        <td class="fw-semibold">{{ $assignment->title }}</td>
                        <td><span class="badge bg-light text-dark">{{ $assignment->subject->name ?? '—' }}</span></td>
                        <td>{{ $assignment->schoolClass->name ?? '—' }}</td>
                        <td class="small">{{ \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('teacher.lms.assignment.submissions', $assignment) }}"
                               class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none">
                                {{ $assignment->submissions_count }} soumission(s)
                            </a>
                        </td>
                        <td>
                            @if($assignment->status === 'graded')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Corrigé</span>
                            @elseif($assignment->status === 'published')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">Publié</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Brouillon</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form action="{{ route('teacher.lms.assignment.destroy', $assignment) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce devoir et toutes ses soumissions ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger action-btn"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- QUIZ ───────────────────────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="tl-quizzes">
        @if($quizzes->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-question-circle fa-3x text-muted mb-3 opacity-50"></i>
                <p class="text-muted mb-3">Aucun quiz créé.</p>
                <a href="{{ route('teacher.lms.quiz.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Créer un quiz
                </a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle content-table mb-0">
                <thead class="table-light small text-muted">
                    <tr>
                        <th>Titre</th>
                        <th>Matière</th>
                        <th>Questions</th>
                        <th>Durée</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quizzes as $quiz)
                    <tr>
                        <td class="fw-semibold">{{ $quiz->title }}</td>
                        <td><span class="badge bg-light text-dark">{{ $quiz->subject->name ?? '—' }}</span></td>
                        <td><span class="badge bg-purple-subtle" style="background:#f5f3ff;color:#7c3aed;">{{ $quiz->questions_count }} Q</span></td>
                        <td class="small text-muted">{{ $quiz->time_limit ? $quiz->time_limit . ' min' : 'Illimitée' }}</td>
                        <td>
                            @if($quiz->is_published)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Publié</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Brouillon</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form action="{{ route('teacher.lms.quiz.destroy', $quiz) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce quiz ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger action-btn"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

@endsection

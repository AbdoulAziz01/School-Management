@extends('admin.layouts.app')

@section('title', 'Emploi du temps — '.$class->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Emplois du temps</a></li>
                            <li class="breadcrumb-item active">{{ $class->name }}</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0">Emploi du temps — {{ $class->name }}</h1>
                    <p class="text-muted mb-0">
                        {{ $class->level->name ?? '' }}
                        @if($class->academicYear) · {{ $class->academicYear->name }} @endif
                    </p>
                </div>
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Grille hebdomadaire --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Aperçu hebdomadaire</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">Horaire</th>
                                    @foreach($days as $dayName)
                                        <th class="text-center">{{ $dayName }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timeSlots as $slot)
                                    <tr>
                                        <td class="text-center bg-light small fw-bold">{{ $slot['label'] }}</td>
                                        @foreach($days as $dayNum => $dayName)
                                            @php $cell = $scheduleGrid[$dayName][$slot['label']] ?? null; @endphp
                                            <td class="p-2 text-center" style="min-width: 130px;">
                                                @if($cell)
                                                    <div class="p-2 rounded bg-primary bg-opacity-10">
                                                        <strong class="text-primary d-block small">{{ $cell['subject']->name ?? '—' }}</strong>
                                                        <small class="text-muted d-block">{{ $cell['teacher']->name ?? '—' }}</small>
                                                        @if($cell['room'])
                                                            <small class="badge bg-secondary">{{ $cell['room'] }}</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- Ajouter un créneau --}}
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Ajouter un créneau</h5>
                        </div>
                        <div class="card-body">
                            @if($subjects->isEmpty())
                                <div class="alert alert-warning mb-0">
                                    Aucune matière pour cette classe. Affectez des matières au niveau ou à la classe d'abord.
                                </div>
                            @else
                                <form method="POST" action="{{ route('admin.schedules.store', $class) }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="day_of_week" class="form-label">Jour <span class="text-danger">*</span></label>
                                        <select name="day_of_week" id="day_of_week" class="form-select @error('day_of_week') is-invalid @enderror" required>
                                            @foreach($days as $num => $name)
                                                <option value="{{ $num }}" {{ (int) old('day_of_week') === $num ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error('day_of_week')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="time_slot" class="form-label">Horaire <span class="text-danger">*</span></label>
                                        <select name="time_slot" id="time_slot" class="form-select @error('time_slot') is-invalid @enderror" required>
                                            @foreach($timeSlots as $slot)
                                                <option value="{{ $slot['label'] }}" {{ old('time_slot') === $slot['label'] ? 'selected' : '' }}>
                                                    {{ $slot['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('time_slot')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="subject_id" class="form-label">Matière <span class="text-danger">*</span></label>
                                        <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                                            @foreach($subjects as $subject)
                                                @php $hasTeacher = ($teachersBySubject[(int) $subject->id] ?? collect())->isNotEmpty(); @endphp
                                                <option value="{{ $subject->id }}"
                                                    {{ (int) old('subject_id', $defaultSubjectId ?? 0) === $subject->id ? 'selected' : '' }}>
                                                    {{ $subject->name }}{{ ! $hasTeacher ? ' — aucun prof affecté' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div id="no-teacher-alert" class="alert alert-warning py-2 small d-none mb-3">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Aucun professeur n'est affecté à cette matière pour {{ $class->name }}.
                                        Allez dans <a href="{{ route('admin.teachers.index') }}" class="alert-link">Enseignants → Modifier</a>
                                        et cochez la matière + la classe, puis enregistrez.
                                    </div>
                                    <div class="mb-3">
                                        <label for="teacher_id" class="form-label">Professeur <span class="text-danger">*</span></label>
                                        <select name="teacher_id" id="teacher_id" class="form-select @error('teacher_id') is-invalid @enderror" required>
                                            <option value="">— Choisir une matière d'abord —</option>
                                        </select>
                                        @error('teacher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="room" class="form-label">Salle</label>
                                        <input type="text" name="room" id="room" class="form-control @error('room') is-invalid @enderror"
                                               value="{{ old('room') }}" placeholder="Ex. Salle A12">
                                        @error('room')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-1"></i> Ajouter le créneau
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Liste des créneaux --}}
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Créneaux enregistrés</h5>
                            <span class="badge bg-secondary">{{ $schedules->count() }}</span>
                        </div>
                        <div class="card-body p-0">
                            @if($schedules->isEmpty())
                                <p class="text-muted text-center py-4 mb-0">Aucun créneau pour cette classe.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Jour</th>
                                                <th>Horaire</th>
                                                <th>Matière</th>
                                                <th>Professeur</th>
                                                <th>Salle</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($schedules as $schedule)
                                                <tr>
                                                    <td>{{ $days[(int) $schedule->day_of_week] ?? '—' }}</td>
                                                    <td class="text-nowrap small">
                                                        {{ \App\Support\ScheduleHelper::formatTime($schedule->start_time) }}
                                                        -
                                                        {{ \App\Support\ScheduleHelper::formatTime($schedule->end_time) }}
                                                    </td>
                                                    <td>{{ $schedule->subject->name ?? '—' }}</td>
                                                    <td>{{ $schedule->teacher->name ?? '—' }}</td>
                                                    <td>{{ $schedule->room ?: '—' }}</td>
                                                    <td class="text-end">
                                                        <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST"
                                                              onsubmit="return confirm('Supprimer ce créneau ?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const teachersBySubject = @json($teachersBySubjectJson);

    function refreshTeachers() {
        const subjectId = document.getElementById('subject_id')?.value;
        const select = document.getElementById('teacher_id');
        const alertBox = document.getElementById('no-teacher-alert');
        if (!select) return;

        select.innerHTML = '<option value="">— Choisir —</option>';
        const list = teachersBySubject[subjectId] || teachersBySubject[String(subjectId)] || [];

        list.forEach(function (t) {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.name;
            if ('{{ old('teacher_id') }}' == t.id) opt.selected = true;
            select.appendChild(opt);
        });

        if (alertBox) {
            if (subjectId && list.length === 0) {
                alertBox.classList.remove('d-none');
                select.disabled = true;
            } else {
                alertBox.classList.add('d-none');
                select.disabled = false;
            }
        }
    }

    document.getElementById('subject_id')?.addEventListener('change', refreshTeachers);
    refreshTeachers();
</script>
@endpush

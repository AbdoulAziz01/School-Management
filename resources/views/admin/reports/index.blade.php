@extends('admin.layouts.app')

@section('title', 'Rapports et exports')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-file-export me-2 text-warning"></i>Rapports et exports</h1>
            <p class="text-muted mb-0">Bulletins PDF, rapport de fin d'année et exports CSV pour l'administration.</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($selectedYear ?? null)
        <p class="text-muted small mb-3">
            <i class="fas fa-info-circle me-1"></i>
            Année active : <strong>{{ $selectedYear->name }}</strong>
            ({{ $selectedYear->statusLabel() }}) — changez-la dans la barre en haut.
        </p>
    @endif

    @if($classes->isEmpty())
        <div class="alert alert-warning">
            Aucune classe pour cette année. Créez des classes avant de générer des rapports.
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="fas fa-file-pdf text-danger me-2"></i>Exports PDF</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="d-grid gap-2">
                            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                            <div class="mb-2">
                                <label class="form-label">Classe</label>
                                <select name="class_id" class="form-select" required>
                                    <option value="">— Choisir —</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Semestre (bulletins)</label>
                                <select name="semester" class="form-select" required>
                                    <option value="1" @selected($currentSemester === 1)>Semestre 1</option>
                                    <option value="2" @selected($currentSemester === 2)>Semestre 2</option>
                                </select>
                            </div>
                            <button type="submit" formaction="{{ route('admin.reports.semester-pdf') }}" class="btn btn-primary">
                                <i class="fas fa-download me-2"></i>Bulletins semestriels (classe complète)
                            </button>
                            <button type="submit" formaction="{{ route('admin.reports.annual-pdf') }}" class="btn btn-outline-primary" formnovalidate>
                                <i class="fas fa-download me-2"></i>Rapport de fin d'année (PDF)
                            </button>
                        </form>
                        <p class="small text-muted mt-3 mb-0">
                            Le PDF semestriel contient un bulletin par élève (même calcul que l'espace élève).
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="fas fa-file-csv text-success me-2"></i>Exports CSV</h5>
                    </div>
                    <div class="card-body d-grid gap-3">
                        @php $classOptions = $classes; $selectedClass = request('class_id'); @endphp
                        <form method="GET" class="border rounded p-3 bg-light">
                            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                            <label class="form-label">Synthèse semestrielle</label>
                            <select name="class_id" class="form-select mb-2" required>
                                <option value="">— Classe —</option>
                                @foreach($classOptions as $class)
                                    <option value="{{ $class->id }}" @selected($selectedClass == $class->id)>{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <select name="semester" class="form-select mb-2" required>
                                <option value="1">Semestre 1</option>
                                <option value="2">Semestre 2</option>
                            </select>
                            <button type="submit" formaction="{{ route('admin.reports.semester-csv') }}" class="btn btn-success w-100">Télécharger CSV</button>
                        </form>
                        <form method="GET" class="border rounded p-3">
                            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                            <label class="form-label">Rapport fin d'année</label>
                            <select name="class_id" class="form-select mb-2" required>
                                <option value="">— Classe —</option>
                                @foreach($classOptions as $class)
                                    <option value="{{ $class->id }}" @selected($selectedClass == $class->id)>{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" formaction="{{ route('admin.reports.annual-csv') }}" class="btn btn-outline-success w-100">Télécharger CSV</button>
                        </form>
                        <form method="GET" class="border rounded p-3">
                            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                            <label class="form-label">Notes détaillées</label>
                            <select name="class_id" class="form-select mb-2" required>
                                <option value="">— Classe —</option>
                                @foreach($classOptions as $class)
                                    <option value="{{ $class->id }}" @selected($selectedClass == $class->id)>{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <select name="semester" class="form-select mb-2">
                                <option value="">Tous les semestres</option>
                                <option value="1">Semestre 1</option>
                                <option value="2">Semestre 2</option>
                            </select>
                            <button type="submit" formaction="{{ route('admin.reports.grades-csv') }}" class="btn btn-outline-secondary w-100">Télécharger CSV</button>
                        </form>
                        <p class="small text-muted mt-3 mb-0">
                            Fichiers UTF-8 avec séparateur point-virgule (;), compatibles Excel.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

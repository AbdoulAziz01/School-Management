@extends('teacher.layouts.app')

@section('title', 'Modifier la note - Enseignant')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Tableau de bord</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teacher.grades.index') }}">Notes</a></li>
            <li class="breadcrumb-item active">Modifier</li>
        </ol>
    </nav>
    
    <h1 class="mb-0 h3">Modifier une note</h1>
    <p class="text-muted">Élève: {{ $grade->user->name ?? 'N/A' }} - Matière: {{ $grade->subject->name ?? 'N/A' }}</p>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modification de la note</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('teacher.grades.update', $grade->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="grade" class="form-label">Note /20 <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="grade" 
                                   id="grade" 
                                   class="form-control form-control-lg @error('grade') is-invalid @enderror" 
                                   value="{{ old('grade', $grade->grade) }}"
                                   min="0" 
                                   max="20" 
                                   step="0.25" 
                                   required>
                            @error('grade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Évaluation</label>
                            <input type="text" class="form-control bg-light" readonly
                                   value="{{ \App\Support\SenegalGradeSequence::LABELS[$grade->type] ?? $grade->type }} — Semestre {{ $grade->semester }}">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="coefficient" class="form-label">Coefficient <span class="text-danger">*</span></label>
                            <input type="number" 
                                   name="coefficient" 
                                   id="coefficient" 
                                   class="form-control @error('coefficient') is-invalid @enderror" 
                                   value="{{ old('coefficient', $grade->coefficient) }}"
                                   min="0.5" 
                                   max="5" 
                                   step="0.5" 
                                   required>
                            @error('coefficient')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" 
                               name="date" 
                               id="date" 
                               class="form-control @error('date') is-invalid @enderror" 
                               value="{{ old('date', $grade->date ? $grade->date->format('Y-m-d') : '') }}"
                               required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="comments" class="form-label">Commentaires</label>
                        <textarea name="comments" 
                                  id="comments" 
                                  class="form-control @error('comments') is-invalid @enderror" 
                                  rows="2" 
                                  placeholder="Remarques sur la note...">{{ old('comments', $grade->comments) }}</textarea>
                        @error('comments')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="appreciation" class="form-label">Appréciation</label>
                        <textarea name="appreciation" 
                                  id="appreciation" 
                                  class="form-control @error('appreciation') is-invalid @enderror" 
                                  rows="2" 
                                  placeholder="Appréciation générale...">{{ old('appreciation', $grade->appreciation) }}</textarea>
                        @error('appreciation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('teacher.grades.index', ['class_id' => $grade->user->class_id, 'subject_id' => $grade->subject_id]) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-trash me-2"></i>Supprimer</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Cette action est irréversible. La note sera définitivement supprimée.</p>
                <form method="POST" action="{{ route('teacher.grades.destroy', $grade->id) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette note ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-trash me-2"></i>Supprimer cette note
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

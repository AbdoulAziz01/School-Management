<!-- resources/views/admin/academic-years/create.blade.php -->
@extends('admin.layouts.app')

@section('content')
<div class="flex-wrap pt-3 pb-2 mb-3 d-flex justify-content-between flex-md-nowrap align-items-center border-bottom">
    <h1 class="h2">Nouvelle année scolaire</h1>
    <div class="mb-2 btn-toolbar mb-md-0">
        <a href="{{ route('academic-years.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('academic-years.store') }}" method="POST">
            @csrf
            
            <div class="mb-3 row">
                <div class="col-md-6">
                    <label for="name" class="form-label">Nom de l'année scolaire *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <div class="pt-2 mt-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" 
                               id="is_current" name="is_current" value="1" 
                               {{ old('is_current') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_current">Définir comme année scolaire en cours</label>
                    </div>
                </div>
            </div>
            
            <div class="mb-3 row">
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Date de début *</label>
                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                           id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                    @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="end_date" class="form-label">Date de fin *</label>
                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                           id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                    @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="gap-2 d-grid d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-outline-secondary me-md-2">
                    <i class="fas fa-undo me-1"></i> Réinitialiser
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Désactiver les dates passées
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').min = today;
        document.getElementById('end_date').min = today;
        
        // Mettre à jour la date de fin minimale lorsque la date de début change
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    });
</script>
@endpush
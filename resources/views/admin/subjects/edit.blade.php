@extends('admin.layouts.app')

@section('title', !empty($isFormationSchool) && $isFormationSchool ? 'Modifier le module' : 'Modifier la matière')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>{{ !empty($isFormationSchool) && $isFormationSchool ? 'Modifier le module' : 'Modifier la matière' }} : {{ $subject->name }}
                    </h5>
                </div>
                <form action="{{ route('admin.subjects.update', $subject) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label">{{ !empty($isFormationSchool) && $isFormationSchool ? 'Nom du module' : 'Nom de la matière' }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $subject->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $subject->code) }}" maxlength="20" required style="text-transform: uppercase;">
                                <div class="form-text">Code unique dans votre établissement.</div>
                                @error('code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="coefficient" class="form-label">Coefficient <span class="text-danger">*</span></label>
                                <input type="number" step="0.5" min="0.5" max="10" class="form-control @error('coefficient') is-invalid @enderror" id="coefficient" name="coefficient" value="{{ old('coefficient', $subject->coefficient) }}" required>
                                @error('coefficient')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="hours_per_week" class="form-label">Heures par semaine</label>
                                <input type="number" step="0.5" min="0" class="form-control @error('hours_per_week') is-invalid @enderror" id="hours_per_week" name="hours_per_week" value="{{ old('hours_per_week', $subject->hours_per_week) }}">
                                @error('hours_per_week')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_core_subject" name="is_core_subject" value="1" {{ old('is_core_subject', $subject->is_core_subject) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_core_subject">{{ !empty($isFormationSchool) && $isFormationSchool ? 'Module principal' : 'Matière principale' }}</label>
                                </div>
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $subject->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        
                        @if(!empty($usesFormationLmd) && $usesFormationLmd)
                            @include('admin.subjects._lmd-settings', ['lmdSettings' => $lmdSettings])
                        @endif

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $subject->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        @if($levels->count() > 0 && (empty($isFormationSchool) || empty($usesFormationLmd)))
                        <div class="mb-3">
                            <label class="form-label">Niveaux associés</label>
                            <div class="row">
                                @php
                                    $selectedLevels = old('levels', $subject->levels->pluck('id')->toArray());
                                @endphp
                                @foreach($levels as $level)
                                    <div class="col-md-4 col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="level_{{ $level->id }}" name="levels[]" value="{{ $level->id }}" {{ in_array($level->id, $selectedLevels) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="level_{{ $level->id }}">{{ $level->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('levels')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

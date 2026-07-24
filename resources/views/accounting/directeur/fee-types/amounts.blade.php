@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Montants — '.$feeType->name)

@section('content')
<a href="{{ route('directeur.fee-types.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Retour
</a>
<div class="mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-coins me-2"></i>{{ $feeType->name }}</h1>
    <p class="text-muted mb-0">Montants pour l'année {{ $academicYear->name }}</p>
</div>

<div class="panel-card">
    <div class="p-4">
        <p class="text-muted small">
            Renseignez un montant « Tous niveaux » pour une valeur par défaut, et/ou un montant spécifique
            par niveau s'il diffère (ex. mensualité plus élevée en Terminale). Un montant de niveau prime
            toujours sur le montant « Tous niveaux ».
        </p>

        <form method="POST" action="{{ route('directeur.fee-types.amounts.update', $feeType) }}">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table table-hover align-middle data-table">
                    <thead>
                        <tr>
                            <th>Niveau</th>
                            <th style="max-width: 220px;">Montant (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Tous niveaux</strong> <span class="class-chip ms-1">valeur par défaut</span></td>
                            <td>
                                <input type="number" step="1" min="0" name="amounts[all]" class="form-control"
                                       value="{{ old('amounts.all', $existingAmounts->get(0)?->amount) }}">
                            </td>
                        </tr>
                        @foreach($levels as $level)
                            <tr>
                                <td>{{ $level->name }}</td>
                                <td>
                                    <input type="number" step="1" min="0" name="amounts[{{ $level->id }}]" class="form-control"
                                           placeholder="Utilise « Tous niveaux » si vide"
                                           value="{{ old('amounts.'.$level->id, $existingAmounts->get($level->id)?->amount) }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn-pill-primary">
                <i class="fas fa-save"></i>Enregistrer les montants
            </button>
        </form>
    </div>
</div>

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection

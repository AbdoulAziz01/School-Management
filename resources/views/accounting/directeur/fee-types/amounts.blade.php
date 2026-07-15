@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Montants — '.$feeType->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-coins me-2"></i>{{ $feeType->name }}</h1>
        <p class="text-muted mb-0">Montants pour l'année {{ $academicYear->name }}</p>
    </div>
    <a href="{{ route('directeur.fee-types.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted small">
            Renseignez un montant « Tous niveaux » pour une valeur par défaut, et/ou un montant spécifique
            par niveau s'il diffère (ex. mensualité plus élevée en Terminale). Un montant de niveau prime
            toujours sur le montant « Tous niveaux ».
        </p>

        <form method="POST" action="{{ route('directeur.fee-types.amounts.update', $feeType) }}">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Niveau</th>
                            <th style="max-width: 220px;">Montant (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-warning">
                            <td><strong>Tous niveaux</strong> <span class="text-muted small">(valeur par défaut)</span></td>
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

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Enregistrer les montants
            </button>
        </form>
    </div>
</div>
@endsection

@extends('admin.layouts.app')

@section('title', 'Aperçu de l\'import')

@section('content')
<div class="container-fluid">
    <p class="mb-3">
        <a href="{{ route('admin.students.import.mapping', $token) }}" class="text-decoration-none small">
            <i class="fas fa-arrow-left me-1"></i> Revenir au mapping des colonnes
        </a>
    </p>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="h4 mb-0"><i class="fas fa-list-check me-2"></i>Aperçu de l'import</h1>
        <div>
            <span class="badge bg-success">{{ $validCount }} ligne(s) valide(s)</span>
            <span class="badge bg-danger">{{ $errorCount }} ligne(s) en erreur</span>
        </div>
    </div>

    @if($errorCount > 0)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Les lignes en erreur ci-dessous ne seront <strong>pas</strong> importées. Corrigez votre fichier et
            recommencez si vous voulez les inclure, ou continuez pour importer uniquement les lignes valides.
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height:60vh;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light" style="position:sticky;top:0;">
                        <tr>
                            <th>Ligne</th>
                            <th>Statut</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Classe</th>
                            <th>Erreurs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr class="{{ !empty($row['errors']) ? 'table-danger' : '' }}">
                                <td>{{ $row['line'] }}</td>
                                <td>
                                    @if(empty($row['errors']))
                                        <span class="badge bg-success">OK</span>
                                    @else
                                        <span class="badge bg-danger">Erreur</span>
                                    @endif
                                </td>
                                <td>{{ $row['data']['last_name'] ?: '—' }}</td>
                                <td>{{ $row['data']['first_name'] ?: '—' }}</td>
                                <td>{{ $row['data']['email'] ?: '—' }}</td>
                                <td>{{ $row['class_display'] ?: '—' }}</td>
                                <td class="small text-danger">
                                    @foreach($row['errors'] as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex gap-2">
            <form method="POST" action="{{ route('admin.students.import.store', $token) }}"
                  onsubmit="return confirm('Importer {{ $validCount }} élève(s) ? Cette action créera directement les comptes.');">
                @csrf
                <button type="submit" class="btn btn-primary" {{ $validCount === 0 ? 'disabled' : '' }}>
                    <i class="fas fa-check me-1"></i> Importer {{ $validCount }} élève(s) valide(s)
                </button>
            </form>
            <a href="{{ route('admin.students.import') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</div>
@endsection

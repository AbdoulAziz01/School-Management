@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Frais scolaires')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Frais scolaires</h1>
        <p class="text-muted mb-0">Types de frais et montants par niveau</p>
    </div>
    <a href="{{ route('directeur.fee-types.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Ajouter un type de frais
    </a>
</div>

<div class="card">
    <div class="card-body">
        @if($feeTypes->isEmpty())
            <div class="alert alert-info mb-0">
                Aucun type de frais configuré. Commencez par ajouter les frais d'inscription, de réinscription et la mensualité.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Récurrent</th>
                            <th style="min-width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($feeTypes as $feeType)
                            <tr>
                                <td><code>{{ $feeType->code }}</code></td>
                                <td>{{ $feeType->name }}</td>
                                <td><span class="badge bg-info">{{ $categories[$feeType->category] ?? $feeType->category }}</span></td>
                                <td>{{ $feeType->is_recurring ? 'Oui (mensuel)' : 'Non' }}</td>
                                <td>
                                    <a href="{{ route('directeur.fee-types.amounts', $feeType) }}" class="btn btn-sm btn-primary" title="Montants par niveau">
                                        <i class="fas fa-coins"></i> Montants
                                    </a>
                                    <a href="{{ route('directeur.fee-types.edit', $feeType) }}" class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('directeur.fee-types.destroy', $feeType) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer ce type de frais ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
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
@endsection

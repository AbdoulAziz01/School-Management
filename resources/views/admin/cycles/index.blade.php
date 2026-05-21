@extends('admin.layouts.app')

@section('title', 'Cycles de formation')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">Cycles de formation</h1>
            <p class="text-muted mb-0">
                Structurez votre établissement par cycles (Année 1, Année 2, module spécialisé…).
                Chaque cycle regroupe ses <strong>modules</strong> et ses <strong>promotions</strong>.
            </p>
        </div>
        <a href="{{ route('admin.cycles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouveau cycle
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="text-primary">1. Cycles</h6>
                    <p class="small text-muted mb-0">Ex. : Année 1, Année 2, Module Bureautique</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-success">2. Modules</h6>
                    <p class="small text-muted mb-0">Matières propres à chaque cycle (menu Matières/Modules)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <h6 class="text-warning">3. Promotions</h6>
                    <p class="small text-muted mb-0">Classes / groupes d'apprenants par cycle</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($levels->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-layer-group fa-3x mb-3 opacity-50"></i>
                    <p class="mb-2">Aucun cycle défini.</p>
                    <a href="{{ route('admin.cycles.create') }}" class="btn btn-sm btn-primary">Créer le premier cycle</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ordre</th>
                                <th>Cycle</th>
                                <th>Description</th>
                                <th class="text-center">Modules</th>
                                <th class="text-center">Promotions</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($levels as $level)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $level->order }}</span></td>
                                    <td><strong>{{ $level->name }}</strong></td>
                                    <td class="small text-muted">{{ Str::limit($level->description, 80) ?: '—' }}</td>
                                    <td class="text-center">{{ $level->subjects_count }}</td>
                                    <td class="text-center">{{ $level->classes_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.cycles.edit', $level) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.cycles.destroy', $level) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Supprimer ce cycle ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
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
@endsection

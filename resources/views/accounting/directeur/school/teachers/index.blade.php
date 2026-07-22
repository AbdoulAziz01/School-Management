@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Enseignants — Directeur')

@section('content')
<a href="{{ route('directeur.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Centre de Commande
</a>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Enseignants</h1>
    <span class="badge bg-secondary fs-6">{{ $teachers->count() }} enseignant(s)</span>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Identifiant</th>
                        <th>Classe(s)</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $teacher)
                        <tr>
                            <td><a href="{{ route('directeur.school.teachers.show', $teacher) }}">{{ $teacher->name }}</a></td>
                            <td><code>{{ $teacher->identifier ?? '-' }}</code></td>
                            <td>
                                @forelse($teacher->resolved_classes->take(3) as $class)
                                    <span class="badge bg-light text-dark border">{{ $class->name }}</span>
                                @empty
                                    <span class="text-muted small">Aucune</span>
                                @endforelse
                                @if($teacher->resolved_classes->count() > 3)
                                    <span class="badge bg-secondary">+{{ $teacher->resolved_classes->count() - 3 }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('directeur.school.teachers.show', $teacher) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

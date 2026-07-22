@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Élèves — Directeur')

@section('content')
<a href="{{ route('directeur.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Centre de Commande
</a>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0"><i class="fas fa-user-graduate me-2"></i>Élèves</h1>
    <span class="badge bg-secondary fs-6">{{ $students->total() }} élève(s)</span>
</div>

<form method="GET" action="{{ route('directeur.school.students.index') }}" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Nom, identifiant..." value="{{ request('search') }}">
    </div>
    <div class="col-md-4">
        <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Toutes les classes</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filtrer</button>
    </div>
</form>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Identifiant</th>
                        <th>Nom</th>
                        <th>Classe</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td><code>{{ $student->identifier ?? '-' }}</code></td>
                            <td><a href="{{ route('directeur.school.students.show', $student) }}">{{ $student->name }}</a></td>
                            <td>{{ $student->schoolClass->name ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('directeur.school.students.show', $student) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 d-flex justify-content-center">
            {{ $students->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

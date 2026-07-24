@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Élèves — Directeur')

@section('content')
<a href="{{ route('directeur.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Centre de Commande
</a>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0"><i class="fas fa-user-graduate me-2"></i>Élèves</h1>
    <span class="count-chip">{{ $students->total() }} élève(s)</span>
</div>

<form method="GET" action="{{ route('directeur.school.students.index') }}" class="search-bar mb-4">
    <div class="search-field">
        <i class="fas fa-search"></i>
        <input type="text" name="search" placeholder="Nom, identifiant..." value="{{ request('search') }}">
    </div>
    <select name="class_id" class="search-select" onchange="this.form.submit()">
        <option value="">Toutes les classes</option>
        @foreach($classes as $class)
            <option value="{{ $class->id }}" {{ (string) request('class_id') === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-pill-primary"><i class="fas fa-filter"></i>Filtrer</button>
</form>

@if($students->isEmpty())
    <div class="empty-state"><i class="fas fa-user-graduate"></i><p class="mb-0">Aucun élève ne correspond à votre recherche.</p></div>
@else
    <div class="card data-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Identifiant</th>
                            <th>Classe</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>
                                    <a href="{{ route('directeur.school.students.show', $student) }}" class="person-cell">
                                        <span class="person-avatar">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                        <span class="person-name">{{ $student->name }}</span>
                                    </a>
                                </td>
                                <td><code>{{ $student->identifier ?? '-' }}</code></td>
                                <td>
                                    @if($student->schoolClass)
                                        <span class="class-chip">{{ $student->schoolClass->name }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('directeur.school.students.show', $student) }}" class="btn-view" title="Voir le profil">
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
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection

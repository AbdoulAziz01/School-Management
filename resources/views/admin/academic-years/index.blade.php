<!-- resources/views/admin/academic-years/index.blade.php -->
@extends('admin.layouts.app')

@section('content')
<div class="flex-wrap pt-3 pb-2 mb-3 d-flex justify-content-between flex-md-nowrap align-items-center border-bottom">
    <h1 class="h2">Gestion des années scolaires</h1>
    <div class="mb-2 btn-toolbar mb-md-0">
        <a href="{{ route('admin.academic-years.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvelle année scolaire
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="mb-4 card">
    <div class="card-header">
        <h5 class="mb-0">Liste des années scolaires</h5>
    </div>
    <div class="p-0 card-body">
        <div class="table-responsive">
            <table class="table mb-0 table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Période</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($years as $year)
                        <tr>
                            <td>{{ $year->name }}</td>
                            <td>
                                {{ $year->start_date->format('d/m/Y') }} - {{ $year->end_date->format('d/m/Y') }}
                            </td>
                            <td>
                                @if($year->is_current)
                                    <span class="badge bg-success">Année en cours</span>
                                @else
                                    <form action="{{ route('admin.academic-years.set-current', $year) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            Définir comme année courante
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.academic-years.show', $year) }}" class="btn btn-outline-primary" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.academic-years.edit', $year) }}" class="btn btn-outline-secondary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.academic-years.destroy', $year) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette année scolaire ?')"
                                                title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center">Aucune année scolaire enregistrée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@extends('admin.layouts.app')

@section('title', 'Étudiants en attente')

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h1 class="mb-0 h3">Étudiants en attente d'approbation</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="shadow-sm card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des étudiants en attente</h5>
            <span class="badge bg-warning text-dark">{{ $students->count() }} en attente</span>
        </div>
        
        <div class="card-body">
            @if($students->isEmpty())
                <div class="mb-0 alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2 fs-4"></i>
                        <span>Aucun étudiant en attente d'approbation.</span>
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0 align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Identifiant</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Date d'inscription</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $user)
                                <tr>
                                    <td><strong class="text-primary">{{ $user->identifier }}</strong></td>
                                    <td>{{ $user->name }}</td>
                                    <td><small>{{ $user->email }}</small></td>
                                    <td>
                                        @if($user->role === 'student')
                                            <span class="badge bg-info">Élève</span>
                                        @elseif($user->role === 'teacher')
                                            <span class="badge bg-success">Enseignant</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <form action="{{ route('admin.approve', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Approuver">
                                                    <i class="fas fa-check me-1"></i> Approuver
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.reject', $user) }}" method="POST" class="d-inline ms-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Rejeter" onclick="return confirm('Êtes-vous sûr de vouloir rejeter {{ $user->name }} ?')">
                                                    <i class="fas fa-times me-1"></i> Rejeter
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($students->hasPages())
                    <div class="pt-3 mt-3 d-flex justify-content-between align-items-center border-top">
                        <div class="text-muted small">
                            Affichage de <b>{{ $students->firstItem() }}</b> à <b>{{ $students->lastItem() }}</b> sur <b>{{ $students->total() }}</b> étudiants
                        </div>
                        <div>
                            {{ $students->links() }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
@endsection
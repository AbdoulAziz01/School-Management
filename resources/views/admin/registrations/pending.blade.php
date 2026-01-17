@extends('admin.layouts.app')

@section('title', 'Inscriptions en attente')

@push('styles')
<style>
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.1);
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: 1.25rem 1.5rem;
    }
    
    .card-header h3 {
        margin: 0;
        font-weight: 600;
        color: #2d3748;
        font-size: 1.25rem;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #6b7280;
        border-top: none;
        padding: 1rem 1.5rem;
    }
    
    .table td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.8125rem;
        line-height: 1.5;
        border-radius: 0.25rem;
    }
    
    .btn i {
        margin-right: 0.25rem;
    }
    
    .alert {
        border: none;
        border-radius: 0.5rem;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="mb-4">
    <h1 class="mb-0 h3">Inscriptions en attente d'approbation</h1>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Debug --}}
@php
    // Vérifier si $pendingUsers est défini et n'est pas vide
    $hasPendingUsers = isset($pendingUsers) && $pendingUsers->isNotEmpty();
@endphp

@if(!isset($pendingUsers))
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>La variable $pendingUsers n'est pas définie
    </div>
@elseif(!$hasPendingUsers)
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>Aucune inscription en attente
    </div>
@endif

@if(isset($pendingUsers) && $pendingUsers->isNotEmpty())
<div class="card">
    <div class="card-header">
        <h3 class="mb-0">
            <i class="fas fa-user-clock me-2"></i>
            Demandes d'inscription en attente ({{ $pendingUsers->count() }})
        </h3>
    </div>
    
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="bg-gray-50">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Classe demandée</th>
                        <th>Date d'inscription</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingUsers as $user)
                        <tr>
                            <td class="text-muted">#{{ $user->identifier }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-light text-primary rounded-circle">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->desired_class)
                                    <span class="badge bg-info">{{ $user->desired_class }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted" data-bs-toggle="tooltip" title="{{ $user->created_at->format('d/m/Y à H:i') }}">
                                    {{ $user->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end">
                                    <button type="button" 
                                            class="btn btn-sm btn-success me-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#approveModal{{ $user->id }}"
                                            data-bs-toggle="tooltip"
                                            title="Approuver et affecter à une classe">
                                        <i class="fas fa-check me-1"></i>
                                        <span class="d-none d-md-inline">Approuver</span>
                                    </button>
                                    
                                    <!-- Modal d'approbation -->
                                    <div class="modal fade" id="approveModal{{ $user->id }}" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.registrations.approve', $user) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="approveModalLabel">Affecter à une classe</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Veuillez sélectionner la classe pour <strong>{{ $user->name }}</strong> :</p>
                                                        <div class="mb-3">
                                                            <label for="class_id" class="form-label">Classe</label>
                                                            <select class="form-select" id="class_id" name="class_id" required>
                                                                <option value="">Sélectionnez une classe</option>
                                                                @foreach(\App\Models\SchoolClass::with('academicYear')
                                                                    ->whereHas('academicYear', function($query) {
                                                                        $query->where('is_current', true);
                                                                    })
                                                                    ->orderBy('name')
                                                                    ->get() as $class)
                                                                    <option value="{{ $class->id }}">
                                                                        {{ $class->name }} ({{ $class->academicYear->name ?? 'N/A' }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                        <button type="submit" class="btn btn-primary">Confirmer l'affectation</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <form action="{{ route('admin.registrations.reject', $user) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger" 
                                                data-bs-toggle="tooltip" 
                                                title="Rejeter l'inscription"
                                                onclick="return confirm('Êtes-vous sûr de vouloir rejeter cette inscription ?')">
                                            <i class="fas fa-times me-1"></i>
                                            <span class="d-none d-md-inline">Rejeter</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    // Activer les tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

@endsection

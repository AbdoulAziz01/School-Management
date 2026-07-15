@extends('admin.layouts.app')

@section('title', 'Comptes du module Comptabilité')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h5 class="mb-0">Comptes Comptabilité (directeur, comptable, caissier)</h5>
                        <a href="{{ route('admin.accounting-staff.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Ajouter un compte
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <form action="{{ route('admin.accounting-staff.index') }}" method="GET" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}" style="min-width: 200px;">
                            <select name="role" class="form-select form-select-sm" style="min-width: 140px;">
                                <option value="">Tous les rôles</option>
                                @foreach($roleLabels as $value => $label)
                                    <option value="{{ $value }}" {{ request('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search') || request('role'))
                                <a href="{{ route('admin.accounting-staff.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </form>
                        <span class="text-muted small">{{ $staff->total() }} compte(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($staff->isEmpty())
                        <div class="alert alert-info">
                            @if(request('search') || request('role'))
                                Aucun compte ne correspond à votre recherche.
                            @else
                                Aucun compte Comptabilité n'a encore été créé.
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Identifiant</th>
                                        <th>Nom</th>
                                        <th>Rôle</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th style="min-width: 140px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staff as $member)
                                        <tr>
                                            <td><code>{{ $member->identifier ?? '-' }}</code></td>
                                            <td>{{ $member->name }}</td>
                                            <td><span class="badge bg-info">{{ $roleLabels[$member->role] ?? $member->role }}</span></td>
                                            <td>{{ $member->email }}</td>
                                            <td>
                                                @if($member->status == 'approved')
                                                    <span class="badge bg-success">Approuvé</span>
                                                @elseif($member->status == 'pending')
                                                    <span class="badge bg-warning">En attente</span>
                                                @else
                                                    <span class="badge bg-danger">Rejeté</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.accounting-staff.show', $member) }}" class="btn btn-sm btn-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.accounting-staff.edit', $member) }}" class="btn btn-sm btn-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" title="Supprimer"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteStaffModal{{ $member->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>

                                                <div class="modal fade" id="deleteStaffModal{{ $member->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <p>Êtes-vous sûr de vouloir supprimer le compte <strong>{{ $member->name }}</strong> ?</p>
                                                                <p class="text-danger"><i class="fas fa-warning me-1"></i>Cette action est irréversible.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <form action="{{ route('admin.accounting-staff.destroy', $member) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">
                                                                        <i class="fas fa-trash me-1"></i>Supprimer
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="mt-4 d-flex justify-content-center">
                                {{ $staff->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

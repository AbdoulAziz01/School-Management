@extends('admin.layouts.app')

@section('title', 'Journal d\'activité — Administration')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-history me-2"></i>Journal d'activité</h1>
        <p class="text-muted mb-0">Traçabilité des modifications sur les élèves et les notes</p>
    </div>
</div>

{{-- Filtres --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.audit-log.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Type de ressource</label>
                <select name="type" class="form-select">
                    <option value="">Tout</option>
                    <option value="App\Models\User" @selected(request('type') === 'App\Models\User')>Élèves</option>
                    <option value="App\Models\Grade" @selected(request('type') === 'App\Models\Grade')>Notes</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Événement</label>
                <select name="event" class="form-select">
                    <option value="">Tout</option>
                    <option value="created" @selected(request('event') === 'created')>Création</option>
                    <option value="updated" @selected(request('event') === 'updated')>Modification</option>
                    <option value="deleted" @selected(request('event') === 'deleted')>Suppression</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filtrer
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.audit-log.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-times me-1"></i> Réinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau des logs --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="fas fa-list me-2"></i>{{ $logs->total() }} entrée(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Événement</th>
                        <th>Ressource</th>
                        <th>Par</th>
                        <th>Champs modifiés</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                    <tr>
                        <td class="text-nowrap">
                            <small>{{ $log->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            @php
                                $eventColors = ['created' => 'success', 'updated' => 'warning', 'deleted' => 'danger'];
                                $eventLabels = ['created' => 'Créé', 'updated' => 'Modifié', 'deleted' => 'Supprimé'];
                            @endphp
                            <span class="badge bg-{{ $eventColors[$log->event] ?? 'secondary' }}">
                                {{ $eventLabels[$log->event] ?? $log->event }}
                            </span>
                        </td>
                        <td>
                            @php
                                $subjectLabel = match($log->subject_type) {
                                    'App\Models\User'  => 'Élève',
                                    'App\Models\Grade' => 'Note',
                                    default            => class_basename($log->subject_type ?? ''),
                                };
                            @endphp
                            <span class="badge bg-secondary">{{ $subjectLabel }}</span>
                            <small class="text-muted ms-1">#{{ $log->subject_id }}</small>
                        </td>
                        <td>
                            @if ($log->causer)
                                <i class="fas fa-user-circle me-1 text-muted"></i>
                                {{ $log->causer->name }}
                            @else
                                <span class="text-muted">Système</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $new = $log->properties['attributes'] ?? [];
                                $old = $log->properties['old'] ?? [];
                            @endphp
                            @if (count($new))
                                <div style="max-width:380px; font-size:0.8rem;">
                                    @foreach ($new as $field => $value)
                                        <div>
                                            <strong>{{ $field }}</strong> :
                                            @if (isset($old[$field]))
                                                <span class="text-danger">{{ is_array($old[$field]) ? json_encode($old[$field]) : $old[$field] }}</span>
                                                <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                            @endif
                                            <span class="text-success">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Aucune activité enregistrée pour le moment.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($logs->hasPages())
    <div class="card-footer d-flex justify-content-center">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection

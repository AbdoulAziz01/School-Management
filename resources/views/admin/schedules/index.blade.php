@extends('admin.layouts.app')

@section('title', 'Emplois du temps')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Emplois du temps</h1>
                    <p class="text-muted mb-0">Saisissez l'emploi du temps par classe — visible chez les professeurs et élèves concernés.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Classes</h5>
                    <span class="text-muted small">{{ $classes->total() }} classe(s)</span>
                </div>
                <div class="card-body p-0">
                    @if($classes->isEmpty())
                        <div class="alert alert-info m-3 mb-0">Aucune classe. Créez d'abord une classe.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Classe</th>
                                        <th>Niveau</th>
                                        <th>Année</th>
                                        <th class="text-center">Créneaux</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classes as $class)
                                        <tr>
                                            <td class="fw-medium">{{ $class->name }}</td>
                                            <td><span class="badge bg-secondary">{{ $class->level->name ?? '—' }}</span></td>
                                            <td class="text-muted">{{ $class->academicYear->name ?? '—' }}</td>
                                            <td class="text-center">
                                                @if($class->schedules_count > 0)
                                                    <span class="badge bg-success">{{ $class->schedules_count }}</span>
                                                @else
                                                    <span class="badge bg-light text-muted">Vide</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.schedules.edit', $class) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-calendar-plus me-1"></i>
                                                    {{ $class->schedules_count > 0 ? 'Modifier' : 'Saisir' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">{{ $classes->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

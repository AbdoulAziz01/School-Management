@extends('platform.layouts.app')

@section('title', 'Cycles — ' . $school->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('platform.schools.show', $school) }}" class="text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i> Retour à l'établissement
    </a>
    <h1 class="h3 mt-2">Cycles de formation — {{ $school->name }}</h1>
    <p class="text-muted mb-0">Super administrateur : définissez la structure (Année 1, Année 2, modules…) avant de créer les promotions.</p>
</div>

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('platform.schools.cycles.create', $school) }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Nouveau cycle
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($levels->isEmpty())
            <p class="text-center text-muted py-5 mb-0">Aucun cycle. Commencez par Année 1 ou un module principal.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Cycle</th>
                            <th>Filière</th>
                            <th>Modules</th>
                            <th>Promotions</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($levels as $level)
                            <tr>
                                <td>{{ $level->order }}</td>
                                <td><strong>{{ $level->name }}</strong></td>
                                <td>{{ $level->serie ?? '—' }}</td>
                                <td>{{ $level->subjects_count }}</td>
                                <td>{{ $level->classes_count }}</td>
                                <td class="text-end">
                                    <a href="{{ route('platform.schools.cycles.edit', [$school, $level]) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

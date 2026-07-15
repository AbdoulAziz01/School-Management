@extends('accounting.layouts.caisse')

@section('title', 'Recherche élève')

@section('content')
<div class="mb-4">
    <a href="{{ route('caisse.dashboard') }}" class="text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i> Retour au guichet
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('caisse.students.search') }}">
            <div class="input-group input-group-lg">
                <input type="text" name="q" class="form-control" placeholder="Nom ou matricule..." value="{{ $search }}" autofocus>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

@if($search !== '')
    <div class="card">
        <div class="card-body">
            @if($students->isEmpty())
                <div class="alert alert-info mb-0">Aucun élève trouvé pour « {{ $search }} ».</div>
            @else
                <div class="list-group">
                    @foreach($students as $student)
                        <a href="{{ route('caisse.students.show', $student) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $student->name }}</div>
                                <div class="small text-muted">
                                    {{ $student->identifier ?? '—' }}
                                    @if($student->schoolClass) · {{ $student->schoolClass->name }} @endif
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
@endsection

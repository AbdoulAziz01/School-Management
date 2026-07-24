@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', $subject->name.' — '.$class->name)

@section('content')
<a href="{{ route('directeur.school.classes.show', $class) }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>{{ $class->name }}
</a>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0"><i class="fas fa-book me-2"></i>{{ $subject->name }} — {{ $class->name }}</h1>
    <span class="count-chip">Barème /{{ $maxGrade }}</span>
</div>

@if($rows->isEmpty())
    <div class="empty-state"><i class="fas fa-book"></i><p class="mb-0">Aucun élève dans cette classe.</p></div>
@else
    <div class="card data-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Nombre de notes</th>
                            <th>Moyenne</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('directeur.school.students.show', $row['student']) }}" class="person-cell">
                                        <span class="person-avatar">{{ strtoupper(substr($row['student']->name, 0, 1)) }}</span>
                                        <span class="person-name">{{ $row['student']->name }}</span>
                                    </a>
                                </td>
                                <td class="text-muted">{{ $row['grades']->count() }}</td>
                                <td>
                                    @if($row['average'] !== null)
                                        <strong>{{ $row['average'] }}/{{ $maxGrade }}</strong>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection

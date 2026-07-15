@extends('admin.layouts.app')

@section('title', 'Cartes Scolaires')

@section('content')
<div class="container-fluid">

    {{-- En-tête --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h4 mb-1 fw-800" style="color:#92400e;">
                <i class="fas fa-id-card me-2" style="color:#f59e0b;"></i>Cartes Scolaires
            </h1>
            <p class="text-muted small mb-0">Aperçu et impression des cartes de tous les élèves</p>
        </div>
        <a href="{{ route('admin.cards.settings') }}"
           class="btn btn-sm fw-700 d-flex align-items-center gap-2"
           style="background:#003087;color:#fff;border-radius:10px;padding:9px 18px;">
            <i class="fas fa-palette"></i> Personnaliser la carte
        </a>
    </div>

    {{-- Filtres --}}
    <div class="card mb-4" style="border:1px solid #fde68a;border-radius:14px;">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.cards.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-600 text-muted mb-1">Recherche</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="Nom, prénom, matricule…"
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-600 text-muted mb-1">Classe</label>
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">Toutes les classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm fw-700 flex-fill"
                            style="background:#f59e0b;color:#1c1917;border-radius:8px;">
                        <i class="fas fa-filter me-1"></i> Filtrer
                    </button>
                    @if(request('search') || request('class_id'))
                        <a href="{{ route('admin.cards.index') }}" class="btn btn-sm btn-outline-secondary flex-fill"
                           style="border-radius:8px;">
                            <i class="fas fa-times me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Compteur --}}
    <p class="text-muted small mb-3">
        <strong>{{ $students->total() }}</strong> élève(s) trouvé(s)
    </p>

    {{-- Grille d'élèves --}}
    @if($students->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-users fa-3x mb-3 d-block" style="color:#fde68a;"></i>
            Aucun élève trouvé.
        </div>
    @else
        <div class="row g-3">
            @foreach($students as $student)
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card h-100 text-center" style="border:1px solid #fde68a;border-radius:14px;transition:all .2s;">
                    <div class="card-body p-3">
                        {{-- Photo / initiales --}}
                        @if($student->profile_photo_path)
                            <img src="{{ Storage::url($student->profile_photo_path) }}"
                                 alt="Photo"
                                 loading="lazy"
                                 class="rounded-circle mb-2 border border-2"
                                 style="width:64px;height:64px;object-fit:cover;border-color:#f59e0b!important;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2"
                                 style="width:64px;height:64px;background:linear-gradient(135deg,#003087,#1a4a9a);color:#fff;font-size:1.4rem;font-weight:800;">
                                {{ strtoupper(mb_substr($student->first_name ?? $student->name, 0, 1)) }}{{ strtoupper(mb_substr($student->last_name ?? '', 0, 1)) }}
                            </div>
                        @endif

                        <div class="fw-700 small text-truncate" style="color:#1c1917;">
                            {{ strtoupper($student->last_name ?? '') }}
                        </div>
                        <div class="small text-muted text-truncate">{{ $student->first_name }}</div>

                        @if($student->schoolClass)
                            <span class="badge mt-1" style="background:#fef3c7;color:#92400e;font-size:.65rem;">
                                {{ $student->schoolClass->name }}
                            </span>
                        @endif

                        <div class="mt-3">
                            <a href="{{ route('admin.cards.show', $student) }}"
                               class="btn btn-sm w-100 fw-700"
                               style="background:#003087;color:#fff;border-radius:8px;font-size:.75rem;">
                                <i class="fas fa-id-card me-1"></i> Voir la carte
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $students->links() }}
        </div>
    @endif

</div>
@endsection

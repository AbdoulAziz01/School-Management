@extends('layouts.student')

@section('title', 'Classes Virtuelles')

@push('styles')
<style>
    .vc-card {
        border-radius:14px; border:1.5px solid #e2e8f0; background:#fff;
        padding:18px 20px; transition:box-shadow .2s; margin-bottom:12px;
    }
    .vc-card:hover { box-shadow:0 4px 18px rgba(0,0,0,.08); }

    .live-badge { background:#dc2626; color:#fff; font-size:.75rem; font-weight:700;
                  padding:4px 12px; border-radius:99px; animation:pulse 1.5s ease infinite;
                  display:inline-flex; align-items:center; gap:6px; }
    @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.55;} }
    .live-dot { width:8px; height:8px; border-radius:50%; background:#fff; }

    .btn-join { background:linear-gradient(135deg,#16a34a,#15803d); border:none; color:#fff;
                font-weight:700; padding:10px 24px; border-radius:10px; transition:all .2s; }
    .btn-join:hover { background:linear-gradient(135deg,#15803d,#166534); color:#fff; transform:translateY(-1px); }
    .btn-join:disabled { background:#d1d5db; transform:none; cursor:not-allowed; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-video me-2 text-warning"></i>Classes Virtuelles</h1>
            <p class="text-muted small mb-0">Rejoignez vos séances de cours en visioconférence</p>
        </div>
    </div>

    @foreach(['success','error'] as $t)
        @if(session($t))
            <div class="alert alert-{{ $t === 'error' ? 'danger' : $t }} alert-dismissible fade show">
                {{ session($t) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{-- Séances actives (EN DIRECT) --}}
    @if($active->isNotEmpty())
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="live-badge"><span class="live-dot"></span>EN DIRECT</span>
            <span class="text-muted small">{{ $active->count() }} séance(s) en cours</span>
        </div>
        @foreach($active as $vc)
        <div class="vc-card" style="border-color:#86efac; background:#f0fdf4;">
            <div class="d-flex align-items-start gap-3 flex-wrap">
                <div style="width:48px;height:48px;border-radius:12px;background:#dcfce7;color:#16a34a;
                            display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                    <i class="fas fa-broadcast-tower"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="fw-bold fs-6" style="color:#1e293b;">{{ $vc->title }}</div>
                    <div class="text-muted small mt-1 d-flex flex-wrap gap-3">
                        <span><i class="fas fa-chalkboard-teacher me-1"></i>
                            {{ $vc->teacher->first_name ?? '' }} {{ $vc->teacher->last_name ?? $vc->teacher->name ?? '—' }}
                        </span>
                        @if($vc->subject)
                            <span><i class="fas fa-book me-1"></i>{{ $vc->subject->name }}</span>
                        @endif
                        <span><i class="fas fa-hourglass me-1"></i>{{ $vc->duration_minutes }} min</span>
                    </div>
                    @if($vc->opened_at)
                        <div class="small mt-1" style="color:#16a34a;">
                            <i class="fas fa-clock me-1"></i>Ouverte depuis {{ $vc->opened_at->diffForHumans() }}
                        </div>
                    @endif
                </div>
                <a href="{{ route('student.virtual-class.join', $vc) }}"
                   class="btn-join btn flex-shrink-0">
                    <i class="fas fa-video me-2"></i>Rejoindre maintenant
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Planifiées --}}
    @if($upcoming->isNotEmpty())
    <div class="mb-4">
        <h6 class="fw-bold mb-3" style="color:#d97706;">
            <i class="fas fa-calendar-alt me-2"></i>Séances planifiées
        </h6>
        @foreach($upcoming as $vc)
        <div class="vc-card">
            <div class="d-flex align-items-start gap-3 flex-wrap">
                <div style="width:48px;height:48px;border-radius:12px;background:#fffbeb;color:#d97706;
                            display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                    <i class="fas fa-clock"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="fw-bold" style="color:#1e293b;">{{ $vc->title }}</div>
                    <div class="text-muted small mt-1 d-flex flex-wrap gap-3">
                        <span><i class="fas fa-chalkboard-teacher me-1"></i>
                            {{ $vc->teacher->first_name ?? '' }} {{ $vc->teacher->last_name ?? $vc->teacher->name ?? '—' }}
                        </span>
                        @if($vc->subject)
                            <span><i class="fas fa-book me-1"></i>{{ $vc->subject->name }}</span>
                        @endif
                        <span><i class="fas fa-calendar me-1"></i>{{ $vc->scheduled_at->format('d/m/Y à H:i') }}</span>
                        <span><i class="fas fa-hourglass me-1"></i>{{ $vc->duration_minutes }} min</span>
                    </div>
                </div>
                <button class="btn btn-sm btn-warning fw-semibold disabled" disabled style="border-radius:10px;">
                    <i class="fas fa-hourglass-start me-1"></i>Pas encore ouverte
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Terminées --}}
    @if($past->isNotEmpty())
    <div>
        <h6 class="fw-bold mb-3 text-muted">
            <i class="fas fa-check-double me-2"></i>Séances passées
        </h6>
        @foreach($past as $vc)
        <div class="vc-card" style="opacity:.75;">
            <div class="d-flex align-items-start gap-3 flex-wrap">
                <div style="width:48px;height:48px;border-radius:12px;background:#f1f5f9;color:#94a3b8;
                            display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                    <i class="fas fa-check"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="fw-semibold" style="color:#475569;">{{ $vc->title }}</div>
                    <div class="text-muted small mt-1 d-flex flex-wrap gap-3">
                        <span><i class="fas fa-chalkboard-teacher me-1"></i>
                            {{ $vc->teacher->first_name ?? '' }} {{ $vc->teacher->last_name ?? $vc->teacher->name ?? '—' }}
                        </span>
                        @if($vc->subject)
                            <span><i class="fas fa-book me-1"></i>{{ $vc->subject->name }}</span>
                        @endif
                        <span><i class="fas fa-calendar me-1"></i>{{ $vc->scheduled_at->format('d/m/Y') }}</span>
                        @if($vc->closed_at)
                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Terminée {{ $vc->closed_at->format('H:i') }}</span>
                        @endif
                    </div>
                </div>
                <span class="badge bg-secondary-subtle text-secondary fw-semibold">Terminée</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Aucune séance --}}
    @if($active->isEmpty() && $upcoming->isEmpty() && $past->isEmpty())
    <div class="card text-center py-5 border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
            <i class="fas fa-video-slash fa-3x text-muted opacity-40 mb-3"></i>
            <h5 class="text-muted">Aucune classe virtuelle planifiée pour votre classe</h5>
            <p class="text-muted small">Vos enseignants n'ont pas encore planifié de séance.</p>
        </div>
    </div>
    @endif

</div>
@endsection

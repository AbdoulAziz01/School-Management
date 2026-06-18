@extends('teacher.layouts.app')

@section('title', 'Classes Virtuelles')

@push('styles')
<style>
    .vc-stat { border-radius:14px; padding:20px; border:1px solid #e2e8f0; }
    .vc-stat .num { font-size:2.2rem; font-weight:800; line-height:1; }
    .vc-stat .lbl { font-size:.78rem; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }

    .vc-row { border-radius:12px; border:1px solid #e2e8f0; background:#fff; padding:16px 20px;
              transition: box-shadow .2s; margin-bottom:10px; }
    .vc-row:hover { box-shadow:0 4px 16px rgba(0,0,0,.07); }

    .live-dot { width:10px; height:10px; border-radius:50%; background:#22c55e;
                animation: liveblink 1.2s ease-in-out infinite; display:inline-block; }
    @keyframes liveblink { 0%,100%{opacity:1;transform:scale(1);} 50%{opacity:.4;transform:scale(.8);} }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-video me-2 text-warning"></i>Classes Virtuelles</h1>
            <p class="text-muted small mb-0">Planifiez et animez vos séances en visioconférence Jitsi Meet</p>
        </div>
        <a href="{{ route('teacher.virtual-class.create') }}" class="btn btn-warning fw-bold" style="border-radius:10px;">
            <i class="fas fa-plus me-2"></i>Nouvelle séance
        </a>
    </div>

    @foreach(['success','error'] as $t)
        @if(session($t))
            <div class="alert alert-{{ $t === 'error' ? 'danger' : $t }} alert-dismissible fade show">
                {{ session($t) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{-- Statistiques --}}
    <div class="row g-3 mb-4">
        @php
            $statCards = [
                ['val'=>$stats['total'],    'lbl'=>'Total',       'bg'=>'#f8fafc',  'color'=>'#1e293b',  'icon'=>'fa-list'],
                ['val'=>$stats['active'],   'lbl'=>'En cours',    'bg'=>'#f0fdf4',  'color'=>'#16a34a',  'icon'=>'fa-broadcast-tower'],
                ['val'=>$stats['upcoming'], 'lbl'=>'Planifiées',  'bg'=>'#fffbeb',  'color'=>'#d97706',  'icon'=>'fa-clock'],
                ['val'=>$stats['past'],     'lbl'=>'Terminées',   'bg'=>'#f8fafc',  'color'=>'#64748b',  'icon'=>'fa-check-double'],
            ];
        @endphp
        @foreach($statCards as $sc)
        <div class="col-6 col-md-3">
            <div class="vc-stat" style="background:{{ $sc['bg'] }};">
                <i class="fas {{ $sc['icon'] }} mb-2" style="color:{{ $sc['color'] }};font-size:1.2rem;"></i>
                <div class="num" style="color:{{ $sc['color'] }};">{{ $sc['val'] }}</div>
                <div class="lbl">{{ $sc['lbl'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Liste --}}
    @if($classes->isEmpty())
        <div class="card text-center py-5 border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body">
                <i class="fas fa-video-slash fa-3x text-muted opacity-40 mb-3"></i>
                <h5 class="text-muted">Aucune classe virtuelle planifiée</h5>
                <a href="{{ route('teacher.virtual-class.create') }}" class="btn btn-warning mt-2" style="border-radius:10px;">
                    <i class="fas fa-plus me-1"></i>Planifier une séance
                </a>
            </div>
        </div>
    @else
        {{-- Séances actives --}}
        @if($classes->where('is_active', true)->isNotEmpty())
        <h6 class="fw-bold text-success mb-2">
            <span class="live-dot me-2"></span>En cours
        </h6>
        @foreach($classes->where('is_active', true) as $vc)
            @include('teacher.virtual-class._row', ['vc' => $vc])
        @endforeach
        <hr class="my-3">
        @endif

        {{-- Planifiées --}}
        @if($classes->filter(fn($c) => $c->isUpcoming())->isNotEmpty())
        <h6 class="fw-bold text-warning mb-2"><i class="fas fa-clock me-1"></i>Planifiées</h6>
        @foreach($classes->filter(fn($c) => $c->isUpcoming()) as $vc)
            @include('teacher.virtual-class._row', ['vc' => $vc])
        @endforeach
        <hr class="my-3">
        @endif

        {{-- Terminées --}}
        @if($classes->filter(fn($c) => $c->isPast())->isNotEmpty())
        <h6 class="fw-bold text-muted mb-2"><i class="fas fa-check-double me-1"></i>Terminées</h6>
        @foreach($classes->filter(fn($c) => $c->isPast()) as $vc)
            @include('teacher.virtual-class._row', ['vc' => $vc])
        @endforeach
        @endif
    @endif

</div>
@endsection

{{-- Sous-vue ligne (utilisée 3x ci-dessus) --}}
@once
@push('scripts')
<script>
// Confirmation suppression
document.querySelectorAll('.delete-vc-form').forEach(f => {
    f.addEventListener('submit', e => {
        if (!confirm('Supprimer cette classe virtuelle ? Cette action est irréversible.')) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
@endonce

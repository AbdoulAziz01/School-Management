@extends('layouts.student')

@section('title', $quiz->title . ' — Quiz')

@push('styles')
<style>
    .quiz-stat-card { text-align:center; padding:20px; border-radius:12px; border:1px solid #e2e8f0; background:#f8fafc; }
    .quiz-stat-card .val  { font-size:2rem; font-weight:800; color:#1e293b; line-height:1; }
    .quiz-stat-card .lbl  { font-size:.75rem; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }
    .attempt-dot { width:22px; height:22px; border-radius:50%; flex-shrink:0; }
    .attempt-dot.used      { background:#ef4444; }
    .attempt-dot.available { background:#22c55e; }
    .btn-start-quiz {
        background: linear-gradient(135deg,#f59e0b,#d97706);
        border: none; color: #fff;
        font-weight: 700; padding: 14px 40px; font-size: 1.05rem;
        border-radius: 12px; transition: all .2s;
    }
    .btn-start-quiz:hover  { background: linear-gradient(135deg,#d97706,#b45309); color:#fff; transform:translateY(-2px); box-shadow:0 8px 24px rgba(217,119,6,.3); }
    .btn-start-quiz:disabled { background:#d1d5db; box-shadow:none; transform:none; cursor:not-allowed; }
</style>
@endpush

@section('content')
<div class="container" style="max-width:860px;">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('student.lms.index') }}" class="text-warning text-decoration-none">
                    <i class="fas fa-graduation-cap me-1"></i>E-Learning
                </a>
            </li>
            <li class="breadcrumb-item active">{{ $quiz->title }}</li>
        </ol>
    </nav>

    @foreach(['success','error','info'] as $type)
        @if(session($type))
            <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show" role="alert">
                {{ session($type) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{-- Carte principale --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:16px; overflow:hidden;">
        <div class="card-header border-0 py-3 px-4"
             style="background:linear-gradient(135deg,#fffbeb,#fff7ed); border-bottom:1px solid #fed7aa;">
            <div class="d-flex align-items-start gap-3">
                <div style="width:54px;height:54px;background:linear-gradient(135deg,#f59e0b,#d97706);
                            border-radius:14px;display:flex;align-items:center;justify-content:center;
                            color:#fff;font-size:1.4rem;flex-shrink:0;">
                    <i class="fas fa-clipboard-question"></i>
                </div>
                <div>
                    <h1 class="h4 fw-bold mb-1" style="color:#1e293b;">{{ $quiz->title }}</h1>
                    <span class="badge" style="background:#fff7ed;color:#d97706;border:1px solid #fed7aa;">
                        <i class="fas fa-book me-1"></i>{{ $quiz->subject->name ?? '—' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            @if($quiz->description)
                <p class="text-muted mb-4">{{ $quiz->description }}</p>
            @endif

            {{-- Statistiques --}}
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="quiz-stat-card">
                        <div class="val">{{ $quiz->questions->count() }}</div>
                        <div class="lbl">Questions</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="quiz-stat-card">
                        <div class="val">{{ number_format($quiz->totalPoints(), 0) }}</div>
                        <div class="lbl">Points</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="quiz-stat-card">
                        @if($quiz->time_limit)
                            <div class="val">{{ $quiz->time_limit }}'</div>
                            <div class="lbl">Durée (min)</div>
                        @else
                            <div class="val"><i class="fas fa-infinity" style="font-size:1.6rem;color:#94a3b8;"></i></div>
                            <div class="lbl">Durée libre</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Jauge de tentatives --}}
            @php $used = 3 - $remainingAttempts; @endphp
            @php
                $alertBg     = $remainingAttempts === 0 ? '#fef2f2' : ($remainingAttempts === 1 ? '#fffbeb' : '#f0fdf4');
                $alertBorder = $remainingAttempts === 0 ? '#fecaca' : ($remainingAttempts === 1 ? '#fed7aa' : '#bbf7d0');
                $alertColor  = $remainingAttempts === 0 ? '#dc2626' : ($remainingAttempts === 1 ? '#d97706' : '#16a34a');
                $alertIcon   = $remainingAttempts === 0 ? 'fa-ban' : ($remainingAttempts === 1 ? 'fa-exclamation-triangle' : 'fa-check-circle');
            @endphp
            <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-4"
                 style="background:{{ $alertBg }};border:1px solid {{ $alertBorder }};">
                <div class="d-flex gap-2 flex-shrink-0">
                    @for($i = 1; $i <= 3; $i++)
                        <div class="attempt-dot {{ $i <= $used ? 'used' : 'available' }}"
                             title="{{ $i <= $used ? 'Tentative utilisée' : 'Disponible' }}"></div>
                    @endfor
                </div>
                <div>
                    <div class="fw-bold" style="color:{{ $alertColor }};">
                        <i class="fas {{ $alertIcon }} me-1"></i>
                        @if($remainingAttempts === 0) Aucune tentative restante
                        @elseif($remainingAttempts === 1) Dernière tentative — réfléchissez bien
                        @else {{ $remainingAttempts }} tentative(s) restante(s) sur 3
                        @endif
                    </div>
                    <div class="small text-muted">Limite stricte : 3 tentatives maximum par quiz</div>
                </div>
            </div>

            {{-- Bouton principal --}}
            <div class="text-center">
                @if($canAttempt)
                    <form action="{{ route('student.quiz.start', $quiz) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-start-quiz">
                            <i class="fas fa-play me-2"></i>Commencer — tentative {{ $used + 1 }}/3
                        </button>
                    </form>
                    @if($remainingAttempts === 1)
                        <p class="text-muted small mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Après cette tentative, vous ne pourrez plus repasser ce quiz.
                        </p>
                    @endif
                @else
                    <button type="button" class="btn btn-start-quiz" disabled>
                        <i class="fas fa-ban me-2"></i>Nombre maximum de tentatives atteint
                    </button>
                    <p class="text-muted small mt-2">Consultez vos résultats ci-dessous.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Historique --}}
    @if($attempts->isNotEmpty())
    <div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
        <div class="card-header py-3 px-4" style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
            <h5 class="mb-0 fw-bold" style="color:#1e293b;">
                <i class="fas fa-history me-2 text-warning"></i>Mes tentatives
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="px-4 py-3 text-muted small">N°</th>
                        <th class="px-4 py-3 text-muted small">Score</th>
                        <th class="px-4 py-3 text-muted small">Résultat</th>
                        <th class="px-4 py-3 text-muted small">Date</th>
                        <th class="px-4 py-3 text-muted small">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $att)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="badge rounded-pill fw-bold px-3"
                                  style="background:#fff7ed;color:#d97706;border:1px solid #fed7aa;font-size:.85rem;">
                                {{ $att->attempt_number }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($att->isCompleted())
                                <strong>{{ number_format((float)$att->score, 1) }}</strong>
                                <span class="text-muted">&nbsp;/&nbsp;{{ number_format((float)$att->max_score, 1) }}</span>
                            @else
                                <em class="text-muted small">En cours…</em>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($att->isCompleted())
                                <span class="badge bg-{{ $att->gradeColor() }}-subtle text-{{ $att->gradeColor() }} fw-bold">
                                    {{ $att->percentage() }}%
                                    <span class="ms-1">{{ $att->gradeLetter() }}</span>
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Non soumis</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-muted small">
                            {{ $att->started_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            @if($att->isCompleted())
                                <a href="{{ route('student.quiz.result', $att) }}"
                                   class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                                    <i class="fas fa-eye me-1"></i>Résultats
                                </a>
                            @else
                                <a href="{{ route('student.quiz.take', $att) }}"
                                   class="btn btn-sm btn-warning fw-semibold" style="border-radius:8px;">
                                    <i class="fas fa-play me-1"></i>Reprendre
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection

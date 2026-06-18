@extends('layouts.student')

@section('title', $vc->title . ' — Séance en direct')

@push('styles')
<style>
    /* On masque le contenu de la page pour maximiser l'espace Jitsi */
    .main-content > .container-fluid { padding:0 !important; }

    .vc-control-bar {
        background:#1c1917; color:#fff; padding:10px 20px;
        display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;
        position:sticky; top:0; z-index:300;
    }
    .live-badge { background:#dc2626; color:#fff; font-size:.75rem; font-weight:700;
                  padding:4px 12px; border-radius:99px; animation:pulse 1.5s ease infinite;
                  display:inline-flex; align-items:center; gap:6px; }
    @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.55;} }

    #jitsi-container { width:100%; height:calc(100vh - 115px); border:none; min-height:480px; }
</style>
@endpush

@section('content')
{{-- Barre de contrôle --}}
<div class="vc-control-bar">
    <div class="d-flex align-items-center gap-3">
        <span class="live-badge">
            <span style="width:7px;height:7px;border-radius:50%;background:#fff;"></span>EN DIRECT
        </span>
        <div>
            <div class="fw-bold" style="font-size:.9rem;">{{ $vc->title }}</div>
            <div style="font-size:.76rem; color:#a8a29e;">
                Animé par {{ $vc->teacher->first_name ?? '' }} {{ $vc->teacher->last_name ?? $vc->teacher->name ?? '' }}
                @if($vc->subject) · {{ $vc->subject->name }} @endif
            </div>
        </div>
    </div>
    <a href="{{ route('student.virtual-class.index') }}"
       class="btn btn-sm btn-outline-light" style="border-radius:8px;"
       onclick="return confirm('Quitter la séance ?')">
        <i class="fas fa-times me-1"></i>Quitter
    </a>
</div>

{{-- Conteneur Jitsi --}}
<div id="jitsi-container"></div>
@endsection

@push('scripts')
<script src="https://meet.jit.si/external_api.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const api = new JitsiMeetExternalAPI('meet.jit.si', {
        roomName:   '{{ $vc->room_name }}',
        width:      '100%',
        height:     '100%',
        parentNode: document.getElementById('jitsi-container'),
        userInfo: {
            displayName: '{{ addslashes(($student->first_name ?? '') . ' ' . ($student->last_name ?? $student->name ?? '')) }}',
            email:       '{{ $student->email }}',
        },
        configOverwrite: {
            prejoinPageEnabled:  false,
            startWithAudioMuted: true,
            startWithVideoMuted: true,
            @if($vc->meeting_password)
            password: '{{ addslashes($vc->meeting_password) }}',
            @endif
        },
        interfaceConfigOverwrite: {
            SHOW_JITSI_WATERMARK:      false,
            SHOW_WATERMARK_FOR_GUESTS: false,
            DEFAULT_REMOTE_DISPLAY_NAME: 'Participant',
            TOOLBAR_BUTTONS: [
                'microphone','camera','desktop','fullscreen',
                'fodeviceselection','hangup','chat','raisehand',
                'tileview','settings','filmstrip',
            ],
        },
    });

    // Rediriger vers la liste quand l'élève raccroche
    api.addEventListener('readyToClose', function () {
        window.location.href = '{{ route('student.virtual-class.index') }}';
    });
});
</script>
@endpush

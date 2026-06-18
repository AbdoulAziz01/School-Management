@extends('teacher.layouts.app')

@section('title', 'Séance — ' . $vc->title)

@push('styles')
<style>
    /* Barre supérieure de contrôle */
    .vc-control-bar {
        background:#1c1917; color:#fff; padding:12px 24px;
        display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
        position:sticky; top:0; z-index:300;
    }
    .live-badge { background:#dc2626; color:#fff; font-size:.78rem; font-weight:700;
                  padding:4px 12px; border-radius:99px; animation:pulse 1.5s ease infinite; }
    @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.6;} }

    /* Conteneur Jitsi */
    #jitsi-container { width:100%; height:calc(100vh - 120px); border:none; min-height:500px; }
</style>
@endpush

@section('content')
{{-- Barre de contrôle --}}
<div class="vc-control-bar">
    <div class="d-flex align-items-center gap-3">
        <span class="live-badge"><i class="fas fa-circle me-1" style="font-size:.5rem;"></i>EN DIRECT</span>
        <div>
            <div class="fw-bold">{{ $vc->title }}</div>
            <div style="font-size:.78rem; color:#a8a29e;">
                {{ $vc->classRoom->name ?? '—' }}
                @if($vc->subject) · {{ $vc->subject->name }} @endif
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($vc->meeting_password)
            <span class="badge bg-secondary">
                <i class="fas fa-lock me-1"></i>{{ $vc->meeting_password }}
            </span>
        @endif
        <form action="{{ route('teacher.virtual-class.toggle', $vc) }}" method="POST">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm btn-danger fw-semibold"
                    onclick="return confirm('Terminer et fermer la séance pour tous les élèves ?')">
                <i class="fas fa-stop me-1"></i>Terminer la séance
            </button>
        </form>
        <a href="{{ route('teacher.virtual-class.index') }}"
           class="btn btn-sm btn-outline-light" style="border-radius:8px;">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>
</div>

{{-- Conteneur Jitsi --}}
<div id="jitsi-container"></div>
@endsection

@push('scripts')
{{-- SDK Jitsi Meet External API --}}
<script src="https://meet.jit.si/external_api.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const domain  = 'meet.jit.si';
    const options = {
        roomName: '{{ $vc->room_name }}',
        width:    '100%',
        height:   '100%',
        parentNode: document.getElementById('jitsi-container'),
        userInfo: {
            displayName: 'Prof. {{ addslashes($teacher->first_name . ' ' . $teacher->last_name) }}',
            email:       '{{ $teacher->email }}',
        },
        configOverwrite: {
            prejoinPageEnabled:    false,
            startWithAudioMuted:   false,
            startWithVideoMuted:   false,
            @if($vc->meeting_password)
            password: '{{ addslashes($vc->meeting_password) }}',
            @endif
        },
        interfaceConfigOverwrite: {
            SHOW_JITSI_WATERMARK:      false,
            SHOW_WATERMARK_FOR_GUESTS: false,
            DEFAULT_REMOTE_DISPLAY_NAME: 'Élève',
            TOOLBAR_BUTTONS: [
                'microphone','camera','desktop','fullscreen',
                'fodeviceselection','hangup','chat','recording',
                'livestreaming','sharedvideo','settings','raisehand',
                'videoquality','filmstrip','feedback','stats','shortcuts',
                'tileview','videobackgroundblur','download','help','mute-everyone',
            ],
        },
    };

    const api = new JitsiMeetExternalAPI(domain, options);

    // Fermer la salle automatiquement quand l'enseignant raccroche
    api.addEventListener('readyToClose', function () {
        // Soumission du formulaire de fermeture
        document.querySelector('form[action*="toggle"]').submit();
    });
});
</script>
@endpush

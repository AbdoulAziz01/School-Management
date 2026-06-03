{{-- Widget React Agent IA (utilisateur authentifié uniquement) --}}
@auth
    <div
        id="ai-agent-widget"
        data-role="{{ auth()->user()->role }}"
        data-school-id="{{ auth()->user()->school_id ?? '' }}"
    ></div>
    @vite(['resources/js/agent-widget.jsx'])
@endauth

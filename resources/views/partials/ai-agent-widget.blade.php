{{-- Widget React Agent IA (admin et super_admin uniquement) --}}
@auth
    @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
    <div
        id="ai-agent-widget"
        data-role="{{ auth()->user()->role }}"
        data-school-id="{{ auth()->user()->school_id ?? '' }}"
    ></div>
    @vite(['resources/js/agent-widget.jsx'])
    @endif
@endauth

    <!-- Logo Header -->
    <div class="sidebar-logo">
        <div class="logo-container">
            <div class="logo-icon-box has-school-logo">
                @if(!empty($schoolLogoDataUri))
                    <img src="{{ $schoolLogoDataUri }}" alt="Logo établissement" class="school-logo-img">
                @else
                    <img src="{{ $platformLogoIcon }}" alt="{{ $platformName }}" class="school-logo-img">
                @endif
            </div>
            <div class="logo-text">
                <h5>{{ $schoolDisplayName ?? 'Mon établissement' }}</h5>
                <small>via {{ $platformName }}</small>
            </div>
        </div>
    </div>

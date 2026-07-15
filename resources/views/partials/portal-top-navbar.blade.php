@php
    $user = Auth::user();
    $initials = strtoupper(substr($user->name ?? 'U', 0, 2));
    $yearStatus = $navbarSelectedYear?->statusLabel();
    $yearStatusClass = match ($navbarSelectedYear?->statusBadgeClass()) {
        'success' => 'is-current',
        'secondary' => 'is-closed',
        default => 'is-past',
    };
@endphp

<header class="portal-top-navbar" aria-label="Navigation principale">
    <div class="portal-navbar-inner">
        @if(!empty($portalShowMenuToggle))
            <button type="button" class="portal-navbar-toggle" id="portalSidebarToggle" aria-label="Ouvrir le menu">
                <i class="fas fa-bars"></i>
            </button>
        @endif

        <div class="portal-navbar-brand d-none d-lg-flex">
            @if(!empty($schoolLogoDataUri))
                <img src="{{ $schoolLogoDataUri }}" alt="" class="portal-navbar-school-logo">
            @else
                <span class="portal-navbar-school-icon"><i class="fas fa-school"></i></span>
            @endif
            <span class="portal-navbar-school-name" title="{{ $schoolDisplayName ?? 'Mon établissement' }}">
                {{ $schoolDisplayName ?? 'Mon établissement' }}
            </span>
        </div>

        @if(($navbarAcademicYears ?? collect())->isNotEmpty())
            <form method="GET" action="{{ url()->current() }}" class="portal-navbar-year-form">
                @foreach(request()->except(['academic_year_id', 'page']) as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $item)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <div class="portal-navbar-year">
                    <label for="portal_navbar_year_{{ $portalScope ?? 'app' }}" class="portal-navbar-year-label">
                        <i class="fas fa-calendar-alt"></i>
                        <span class="portal-navbar-year-label-text">Année</span>
                    </label>
                    <select
                        id="portal_navbar_year_{{ $portalScope ?? 'app' }}"
                        name="academic_year_id"
                        class="portal-navbar-year-select"
                        onchange="this.form.submit()"
                        aria-label="Choisir l'année scolaire"
                    >
                        @foreach($navbarAcademicYears as $year)
                            <option value="{{ $year->id }}" @selected($navbarSelectedYear?->id === $year->id)>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($navbarSelectedYear && $yearStatus)
                        <span class="portal-navbar-year-status {{ $yearStatusClass }}">{{ $yearStatus }}</span>
                    @endif
                </div>
            </form>
        @endif

        <div class="portal-navbar-spacer" aria-hidden="true"></div>

        @php
            $unreadNotifications = $user->unreadNotifications()->latest()->take(8)->get();
            $unreadNotificationsCount = $user->unreadNotifications()->count();
        @endphp
        <div class="portal-navbar-notif dropdown">
            <button
                type="button"
                class="portal-navbar-notif-trigger"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Notifications{{ $unreadNotificationsCount > 0 ? ' (' . $unreadNotificationsCount . ' non lues)' : '' }}"
            >
                <i class="fas fa-bell"></i>
                @if($unreadNotificationsCount > 0)
                    <span class="portal-navbar-notif-badge">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow border-0 portal-navbar-notif-dropdown">
                <div class="portal-navbar-notif-header">
                    <span class="fw-semibold small">Notifications</span>
                    @if($unreadNotificationsCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm p-0 small text-decoration-none">Tout marquer comme lu</button>
                        </form>
                    @endif
                </div>
                <div class="portal-navbar-notif-list">
                    @forelse($unreadNotifications as $notification)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="portal-navbar-notif-item">
                                {{ $notification->data['message'] ?? 'Nouvelle notification' }}
                                <span class="portal-navbar-notif-item-time">{{ $notification->created_at->diffForHumans() }}</span>
                            </button>
                        </form>
                    @empty
                        <div class="portal-navbar-notif-empty">Aucune notification.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="portal-navbar-user dropdown">
            <button
                type="button"
                class="portal-navbar-user-trigger"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Menu compte"
            >
                <span class="portal-navbar-avatar">{{ $initials }}</span>
                <span class="portal-navbar-identity d-none d-md-flex">
                    <span class="portal-navbar-name">{{ $user->name }}</span>
                    <span class="portal-navbar-meta-line">
                        <span class="portal-navbar-badge">{{ $portalRoleLabel }}</span>
                        @if(!empty($portalRoleSubLabel))
                            <span class="portal-navbar-sublabel">{{ $portalRoleSubLabel }}</span>
                        @endif
                    </span>
                </span>
                <i class="fas fa-chevron-down portal-navbar-chevron d-none d-md-inline"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2 portal-navbar-dropdown">
                <li class="dropdown-header portal-navbar-dropdown-header d-md-none">
                    <div class="fw-semibold text-truncate">{{ $user->name }}</div>
                    <div class="small text-muted">
                        {{ $portalRoleLabel }}
                        @if(!empty($portalRoleSubLabel))
                            · {{ $portalRoleSubLabel }}
                        @endif
                    </div>
                </li>
                <li class="d-md-none"><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item" href="{{ $portalProfileRoute }}">
                        <i class="fas fa-user-circle me-2 text-warning"></i>Mon profil
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item text-danger" href="{{ route('logout.get') }}">
                        <i class="fas fa-power-off me-2"></i>Déconnexion
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

@php
    $pendingCredentials = $pendingCredentials ?? null;
    $canViewPassword = $canViewPassword ?? (auth()->user()?->canViewUserPasswords() ?? false);
    $roleLabel = $roleLabel ?? 'utilisateur';
@endphp

<div class="user-credentials-panel">
    @if($canViewPassword)
        @if($pendingCredentials)
            <div class="border border-warning rounded p-3 mb-0 bg-warning bg-opacity-10">
                <p class="small fw-semibold mb-2 text-dark">
                    <i class="fas fa-key me-1"></i> Identifiants de connexion
                </p>
                <p class="small text-danger fw-semibold mb-2">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Affiché une seule fois : notez-le ou envoyez-le maintenant, il ne sera plus jamais visible ici ensuite.
                </p>
                <ul class="list-unstyled small mb-3">
                    <li class="mb-1">
                        <span class="text-muted">Identifiant :</span>
                        <code class="user-select-all">{{ $pendingCredentials['identifier'] ?? '—' }}</code>
                    </li>
                    <li class="mb-1">
                        <span class="text-muted">Email :</span>
                        {{ $pendingCredentials['email'] ?? '—' }}
                    </li>
                    <li>
                        <span class="text-muted">Mot de passe :</span>
                        <code class="user-select-all fs-6">{{ $pendingCredentials['password'] ?? '—' }}</code>
                    </li>
                </ul>
                @if(!empty($schoolCode))
                    <p class="small text-muted mb-2">
                        <span class="text-muted">Code établissement (connexion) :</span>
                        <code class="user-select-all">{{ $schoolCode }}</code>
                    </p>
                @endif
                <p class="small text-muted mb-3">
                    Connexion avec l'identifiant ou l'email{{ !empty($schoolCode) ? ', et le code établissement si le matricule existe ailleurs' : '' }}.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    @if(!empty($sendRoute) && $user->email)
                        <form action="{{ $sendRoute }}" method="POST"
                              onsubmit="return confirm('Envoyer identifiant et mot de passe à {{ $user->email }} ?');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-envelope me-1"></i> Envoyer par email
                            </button>
                        </form>
                    @endif
                    @if(!empty($regenerateRoute))
                        <form action="{{ $regenerateRoute }}" method="POST"
                              onsubmit="return confirm('Générer un nouveau mot de passe ? L\'ancien ne fonctionnera plus.');">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-sync me-1"></i> Régénérer
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @else
            <p class="small text-muted mb-2">
                Identifiant : <code>{{ $user->identifier ?? '—' }}</code>
            </p>
            @if($user->invitation_email_sent_at ?? false)
                <p class="small text-muted mb-2">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    Dernier envoi email le {{ $user->invitation_email_sent_at->format('d/m/Y à H:i') }}.
                </p>
            @endif
            <p class="small text-muted mb-2">
                <i class="fas fa-info-circle me-1"></i>
                Aucun mot de passe enregistré. Générez-en un pour le consulter ici et le communiquer à l'{{ $roleLabel }}.
            </p>
            <div class="d-flex flex-wrap gap-2">
                @if(!empty($regenerateRoute))
                    <form action="{{ $regenerateRoute }}" method="POST"
                          onsubmit="return confirm('Générer un mot de passe temporaire ?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-key me-1"></i> Générer le mot de passe
                        </button>
                    </form>
                @endif
                @if(!empty($sendRoute) && $user->email)
                    <form action="{{ $sendRoute }}" method="POST"
                          onsubmit="return confirm('Générer un mot de passe et l\'envoyer à {{ $user->email }} ?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i> Envoyer par email
                        </button>
                    </form>
                @endif
            </div>
        @endif
    @else
        <p class="small text-muted mb-0">
            Identifiant : <code>{{ $user->identifier ?? '—' }}</code>
        </p>
    @endif
</div>

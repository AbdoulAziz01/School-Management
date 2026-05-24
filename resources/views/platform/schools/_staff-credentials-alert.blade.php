@php
    $credentials = session('staff_credentials') ?? session('new_admin_login');
@endphp
@if($credentials)
    @php
        $title = $credentials['title'] ?? 'Identifiants de connexion';
        $password = $credentials['password'] ?? $credentials['otp_code'] ?? null;
        $emailSent = $credentials['email_sent'] ?? $credentials['otp_sent'] ?? false;
    @endphp
    <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
        <h6 class="alert-heading mb-3">
            <i class="fas fa-key me-2"></i>{{ $title }}
        </h6>
        <p class="mb-3 small text-muted">
            Communiquez ces informations à la personne concernée. Elle pourra se connecter avec l’<strong>identifiant</strong> ou l’<strong>email</strong>, et le <strong>mot de passe</strong> ci-dessous.
        </p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered bg-white mb-3 mb-0">
                <tbody>
                    @if(!empty($credentials['school_name']))
                        <tr>
                            <th class="text-muted" style="width:38%">Établissement</th>
                            <td><strong>{{ $credentials['school_name'] }}</strong></td>
                        </tr>
                    @endif
                    @if(!empty($credentials['school_code']))
                        <tr>
                            <th class="text-muted">Code inscription établissement</th>
                            <td><code class="fs-6 user-select-all">{{ $credentials['school_code'] }}</code></td>
                        </tr>
                    @endif
                    @if(!empty($credentials['name']))
                        <tr>
                            <th class="text-muted">Nom</th>
                            <td>{{ $credentials['name'] }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th class="text-muted">Email</th>
                        <td><code class="user-select-all">{{ $credentials['email'] ?? '—' }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Identifiant</th>
                        <td><code class="fs-6 user-select-all">{{ $credentials['identifier'] ?? '—' }}</code></td>
                    </tr>
                    @if($password)
                        <tr>
                            <th class="text-muted">Mot de passe</th>
                            <td>
                                <code class="fs-5 fw-bold user-select-all text-primary">{{ $password }}</code>
                                <span class="text-muted small d-block mt-1">Code à 6 chiffres (identique à l’OTP envoyé par email si l’envoi a réussi).</span>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @if($emailSent)
            <p class="mb-0 small"><i class="fas fa-envelope-circle-check me-1"></i> Un email avec ce code a également été envoyé à {{ $credentials['email'] ?? "l'adresse indiquée" }}.</p>
        @elseif(!empty($credentials['mail_error']))
            <p class="mb-0 small text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Email non envoyé : {{ $credentials['mail_error'] }} — transmettez le mot de passe manuellement.</p>
        @elseif($password)
            <p class="mb-0 small text-muted"><i class="fas fa-info-circle me-1"></i> Transmettez le mot de passe manuellement si aucun email n’a été envoyé.</p>
        @endif
    </div>
@endif

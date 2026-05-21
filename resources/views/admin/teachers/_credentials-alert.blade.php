@if(session('teacher_created') || session('teacher_credentials'))
    @php $creds = session('teacher_created') ?? session('teacher_credentials'); @endphp
    <div class="alert alert-warning alert-no-autoclose" role="alert">
        <strong><i class="fas fa-key me-1"></i> Identifiants de connexion — {{ $creds['name'] }}</strong>
        @if(!empty($creds['email_sent']))
            <p class="mb-1 mt-2 small text-success">Email également envoyé à {{ $creds['email'] }}.</p>
        @elseif(!empty($creds['mail_error']))
            <p class="mb-1 mt-2 small text-danger">{{ $creds['mail_error'] }}</p>
        @else
            <p class="mb-1 mt-2 small">Ouvrez la fiche de l'enseignant pour consulter et conserver les identifiants.</p>
        @endif
        <ul class="mb-0 mt-2">
            <li><strong>Identifiant :</strong> <code class="user-select-all">{{ $creds['identifier'] ?? '—' }}</code></li>
            <li><strong>Email :</strong> {{ $creds['email'] ?? '—' }}</li>
            <li><strong>Mot de passe temporaire :</strong> <code class="user-select-all fs-6">{{ $creds['password'] ?? '—' }}</code></li>
        </ul>
    </div>
@endif

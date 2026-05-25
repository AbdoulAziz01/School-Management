@if(!empty($loginCredentials) && !empty($loginCredentials['staff']))
    <div id="school-login-credentials" class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="fas fa-key text-primary me-2"></i>Identifiants de connexion
            </h5>
            @if(($loginCredentials['source'] ?? '') === 'load_test' || ($loginCredentials['source'] ?? '') === 'load_test_inferred')
                <span class="badge bg-light text-dark border">Jeu de données charge</span>
            @endif
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Connexion établissement : utilisez l’<strong>email</strong> ou l’<strong>identifiant</strong> avec le mot de passe ci-dessous.
                @if(($loginCredentials['source'] ?? '') === 'load_test_inferred')
                    <span class="d-block mt-1">Mot de passe du seeder de démonstration (non régénéré).</span>
                @endif
            </p>

            <div class="table-responsive mb-3">
                <table class="table table-sm table-bordered bg-white mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Rôle</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Identifiant</th>
                            <th>Mot de passe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loginCredentials['staff'] as $row)
                            <tr>
                                <td>
                                    @if(($row['role'] ?? '') === 'admin' || ($row['role'] ?? '') === \App\Models\User::ROLE_ADMIN)
                                        <span class="badge bg-primary">Admin</span>
                                    @else
                                        <span class="badge bg-info text-dark">{{ $row['role'] ?? 'Staff' }}</span>
                                    @endif
                                </td>
                                <td>{{ $row['name'] ?? '—' }}</td>
                                <td><code class="user-select-all small">{{ $row['email'] ?? '—' }}</code></td>
                                <td><code class="user-select-all small">{{ $row['identifier'] ?? '—' }}</code></td>
                                <td>
                                    <code class="fs-6 fw-bold text-primary user-select-all">{{ $row['password'] ?? '—' }}</code>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(!empty($loginCredentials['student_default_password']))
                <div class="alert alert-light border mb-0 py-2">
                    <strong class="small">Élèves / étudiants (démo)</strong>
                    <ul class="small mb-0 mt-1">
                        <li>Email : <code class="user-select-all">{{ $loginCredentials['student_email_hint'] ?? '*@école.edu.sn' }}</code></li>
                        <li>Mot de passe : <code class="fw-bold text-primary user-select-all">{{ $loginCredentials['student_default_password'] }}</code></li>
                    </ul>
                </div>
            @endif

            @if(!empty($school->code))
                <p class="small text-muted mb-0 mt-2">
                    Code inscription établissement : <code class="user-select-all">{{ $school->code }}</code>
                </p>
            @endif
        </div>
    </div>
@endif

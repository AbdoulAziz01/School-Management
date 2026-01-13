<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - School Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(to bottom right, #1e40af, #3b82f6, #93c5fd, #ffffff);
            background-attachment: fixed;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="py-5 row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="p-5 auth-card">
                    <div class="mb-4 text-center">
                        <h2 class="fw-bold text-primary">Créer un compte</h2>
                        <p class="text-muted">Rejoignez notre système de gestion scolaire</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Nom complet -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom complet *</label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Adresse email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email *</label>
                            <input type="email" 
                                   class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Rôle -->
                        <div class="mb-3">
                            <label for="role" class="form-label">Je suis un(e) *</label>
                            <select class="form-select form-select-lg @error('role') is-invalid @enderror" 
                                    id="role" 
                                    name="role" 
                                    required
                                    onchange="toggleRoleFields()">
                                <option value="">-- Choisir un rôle --</option>
                                <option value="eleve" {{ old('role') == 'eleve' ? 'selected' : '' }}>Élève</option>
                                <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Professeur</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Champ classe pour élève -->
                        <div class="mb-3" id="class-field" style="display: {{ old('role') == 'eleve' ? 'block' : 'none' }};">
                            <label for="desired_class" class="form-label">Classe souhaitée *</label>
                            <select class="form-select form-select-lg @error('desired_class') is-invalid @enderror" 
                                    id="desired_class" 
                                    name="desired_class"
                                    {{ old('role') == 'eleve' ? 'required' : '' }}>
                                <option value="">-- Choisir une classe --</option>
                                <option value="6e" {{ old('desired_class') == '6e' ? 'selected' : '' }}>6ème</option>
                                <option value="5e" {{ old('desired_class') == '5e' ? 'selected' : '' }}>5ème</option>
                                <option value="4e" {{ old('desired_class') == '4e' ? 'selected' : '' }}>4ème</option>
                                <option value="3e" {{ old('desired_class') == '3e' ? 'selected' : '' }}>3ème</option>
                                <option value="Seconde" {{ old('desired_class') == 'Seconde' ? 'selected' : '' }}>Seconde</option>
                                <option value="Première" {{ old('desired_class') == 'Première' ? 'selected' : '' }}>Première</option>
                                <option value="Terminale" {{ old('desired_class') == 'Terminale' ? 'selected' : '' }}>Terminale</option>
                            </select>
                            @error('desired_class')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Champ matières pour professeur -->
                        <div class="mb-3" id="subjects-field" style="display: {{ old('role') == 'teacher' ? 'block' : 'none' }};">
                            <label class="form-label">Matières enseignées *</label>
                            <small class="mb-2 text-muted d-block">Sélectionnez les matières que vous enseignez</small>
                            @php
                                $oldSubjects = old('subjects', []);
                            @endphp
                            @foreach($subjects as $subject)
                                <div class="form-check">
                                    <input class="form-check-input @error('subjects') is-invalid @enderror" 
                                           type="checkbox" 
                                           name="subjects[]" 
                                           value="{{ $subject->id }}" 
                                           id="subject_{{ $subject->id }}"
                                           {{ in_array($subject->id, $oldSubjects) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="subject_{{ $subject->id }}">
                                        {{ $subject->name }}
                                    </label>
                                </div>
                            @endforeach
                            @error('subjects')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mot de passe -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe *</label>
                            <input type="password" 
                                   class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required
                                   autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirmer le mot de passe -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe *</label>
                            <input type="password" 
                                   class="form-control form-control-lg" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required
                                   autocomplete="new-password">
                        </div>

                        <!-- Bouton d'inscription -->
                        <div class="mb-3 d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Créer mon compte
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-decoration-none">
                                Déjà inscrit ? <strong>Se connecter</strong>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="mt-3 text-center">
                    <a href="{{ url('/') }}" class="text-white text-decoration-none">
                        ← Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleRoleFields() {
        const roleSelect = document.getElementById('role');
        const classField = document.getElementById('class-field');
        const subjectsField = document.getElementById('subjects-field');
        const classSelect = document.getElementById('desired_class');

        roleSelect.addEventListener('change', function() {
            if (this.value === 'eleve') {
                classField.style.display = 'block';
                classSelect.required = true;
                subjectsField.style.display = 'none';
            } else if (this.value === 'teacher') {
                classField.style.display = 'none';
                classSelect.required = false;
                subjectsField.style.display = 'block';
            } else {
                classField.style.display = 'none';
                subjectsField.style.display = 'none';
                classSelect.required = false;
            }
        });
    }

    // Initialiser les champs au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        toggleRoleFields();
    });
    </script>
</body>
</html>
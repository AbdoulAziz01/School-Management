<x-guest-layout>
    <h1 class="mb-4 text-center h4">Créer un compte</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Nom --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nom complet</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                class="form-control"
                placeholder="Ex : Abdoul Aziz Gueye"
            >
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Adresse email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                class="form-control"
                placeholder="exemple@ecole.com"
            >
        </div>

            <select name="role" id="role" required>
                <option value="">Choisir un rôle </option>
                <option value="student">Élève</option>
                <option value="teacher">Professeur</option>
            </select>

        {{-- Mot de passe --}}
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                class="form-control"
                placeholder="Choisissez un mot de passe"
            >
        </div>

        {{-- Confirmation --}}
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                class="form-control"
                placeholder="Répétez le mot de passe"
            >
        </div>

        <div class="mb-3 d-grid">
            <button type="submit" class="btn btn-success">
                Créer mon compte
            </button>
        </div>

        <p class="mb-0 text-center text-muted">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="text-decoration-none">Se connecter</a>
        </p>
    </form>
</x-guest-layout>

<x-guest-layout>
    <h1 class="mb-4 text-center h4">Connexion a Mohamad PSL School</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
    <label for="identifier" class="form-label">Identifiant</label>
    <input
        id="identifier"
        type="text"
        name="identifier"
        value="{{ old('identifier') }}"
        required
        autofocus
        class="form-control"
        placeholder="Ex: E2024001 ou P2024001"
    >
</div>


        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                class="form-control"
                placeholder="Votre mot de passe"
            >
        </div>

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">
                    Se souvenir de moi
                </label>
            </div>

            @if (Route::has('password.request'))
                <a class="small text-decoration-none" href="{{ route('password.request') }}">
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <div class="mb-3 d-grid">
            <button type="submit" class="btn btn-primary">
                Se connecter
            </button>
        </div>

        <p class="mb-0 text-center text-muted">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="text-decoration-none">Créer un compte</a>
        </p>
    </form>
</x-guest-layout>

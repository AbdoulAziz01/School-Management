<x-mail::message>
# Bonjour {{ $teacher->name }}

Votre compte enseignant sur **{{ config('app.name') }}** est prêt.

**Identifiant de connexion :** {{ $teacher->identifier }}

**Mot de passe temporaire :** {{ $plainPassword }}

Utilisez l'identifiant ci-dessus ou votre adresse email (**{{ $teacher->email }}**) pour vous connecter.

Ce message est personnel et confidentiel : **ne partagez pas** ces informations.

<x-mail::button :url="url('/login')">
Accéder à la connexion
</x-mail::button>

Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>

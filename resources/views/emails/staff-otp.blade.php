<x-mail::message>
# Bonjour {{ $user->name }}

Votre compte **{{ $accountLabel }}** sur **{{ config('app.name') }}** est prêt.

**Identifiant de connexion :** {{ $user->identifier }}

**Code OTP (mot de passe temporaire) :** {{ $otpCode }}

Connectez-vous avec votre adresse email (**{{ $user->email }}**) ou votre identifiant, en utilisant le code OTP ci-dessus.

Ce message est personnel et confidentiel : **ne partagez pas** ce code.

<x-mail::button :url="url('/login')">
Accéder à la connexion
</x-mail::button>

Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>

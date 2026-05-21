<x-mail::message>
# Bonjour {{ $student->name }}

Votre compte élève sur **{{ config('app.name') }}** est prêt.

**Identifiant de connexion :** {{ $student->identifier }}

**Mot de passe :** {{ $plainPassword }}

@if($student->email)
Utilisez l'identifiant ci-dessus ou votre adresse email (**{{ $student->email }}**) pour vous connecter.
@else
Utilisez l'identifiant ci-dessus pour vous connecter.
@endif

@if($student->school?->code)
Si plusieurs établissements partagent le même type de matricule, indiquez aussi le **code établissement : {{ $student->school->code }}** sur la page de connexion.
@endif

Ce message est personnel et confidentiel : **ne partagez pas** ces informations.

<x-mail::button :url="url('/login')">
Accéder à la connexion
</x-mail::button>

Merci,<br>
{{ config('app.name') }}
</x-mail::message>

@php
    $pendingCredentials = $pendingCredentials ?? null;
    $canViewPassword = $canViewPassword ?? (auth()->user()?->canViewUserPasswords() ?? false);
@endphp

@include('admin.partials._user-credentials-panel', [
    'user' => $teacher,
    'pendingCredentials' => $pendingCredentials,
    'canViewPassword' => $canViewPassword,
    'roleLabel' => 'enseignant',
    'regenerateRoute' => route('admin.teachers.regenerate-credentials', $teacher),
    'sendRoute' => route('admin.teachers.send-invitation', $teacher),
])

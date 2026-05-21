@php
    $pendingCredentials = $pendingCredentials ?? null;
    $canViewPassword = $canViewPassword ?? (auth()->user()?->canViewUserPasswords() ?? false);
@endphp

@include('admin.partials._user-credentials-panel', [
    'user' => $student,
    'pendingCredentials' => $pendingCredentials,
    'canViewPassword' => $canViewPassword,
    'roleLabel' => 'élève',
    'schoolCode' => $schoolCode ?? null,
    'regenerateRoute' => route('admin.students.regenerate-credentials', $student),
    'sendRoute' => route('admin.students.send-credentials', $student),
])

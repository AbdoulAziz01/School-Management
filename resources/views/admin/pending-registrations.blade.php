<x-app-layout>
    <x-slot name="header">
        <h1 class="mb-0 h3 fw-bold">Inscriptions en Attente</h1>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="border-0 shadow-sm card">
        <div class="card-body">
            @if($pendingUsers->isEmpty())
                <p class="text-muted">Aucune inscription en attente.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Identifiant</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingUsers as $user)
                            <tr>
                                <td><strong>{{ $user->identifier }}</strong></td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge bg-info">{{ $user->role }}</span></td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.approve', $user) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Valider</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reject', $user) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Rejeter</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

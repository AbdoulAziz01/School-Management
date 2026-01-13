<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Liste des élèves</h5>
        <span class="badge bg-primary">{{ $students->total() }} élève(s)</span>
    </div>
    
    <div class="card-body">
        @if($students->isEmpty())
            <div class="mb-0 alert alert-info">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-2 fs-4"></i>
                    <span>Aucun élève n'a été enregistré pour le moment.</span>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table mb-0 align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Identifiant</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Classe</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td><strong class="text-primary">{{ $student->identifier ?? 'N/A' }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $student->name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td><small>{{ $student->email }}</small></td>
                                <td>
                                    @if($student->class)
                                        <span class="badge bg-primary">{{ $student->class->name ?? 'N/A' }}</span>
                                    @else
                                        <span class="badge bg-secondary">Non affecté</span>
                                    @endif
                                </td>
                                <td>
                                    @if($student->status === 'approved')
                                        <span class="badge bg-success">Actif</span>
                                    @elseif($student->status === 'pending')
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    @else
                                        <span class="badge bg-danger">Inactif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer {{ $student->name }} ?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4" id="pagination-container">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>

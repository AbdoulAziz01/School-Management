<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Liste des élèves</h5>
        <span class="badge" style="background-color: #fd7e14;">
            @if(!empty($search))
                {{ $students->total() }} résultat(s)
            @else
                {{ $students->total() }} élève(s)
            @endif
        </span>
    </div>
    
    <div class="card-body">
        @if($students->isEmpty())
            <div class="mb-0 alert alert-{{ !empty($search) ? 'warning' : 'info' }}">
                <div class="d-flex align-items-center">
                    <i class="fas fa-{{ !empty($search) ? 'search' : 'info-circle' }} me-2 fs-4"></i>
                    <span>
                        @if(!empty($search))
                            Aucun élève trouvé pour « {{ $search }} ». Vérifiez l'orthographe ou choisissez une suggestion ci-dessous.
                        @else
                            Aucun élève n'a été enregistré pour le moment.
                        @endif
                    </span>
                </div>
            </div>

            @if(!empty($search) && isset($searchSuggestions) && $searchSuggestions->isNotEmpty())
                <div class="mt-3">
                    <p class="mb-2 fw-semibold text-muted small">
                        <i class="fas fa-lightbulb me-1 text-warning"></i> Peut-être cherchiez-vous :
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($searchSuggestions as $suggestion)
                            <a href="{{ route('admin.students.index', ['search' => $suggestion->name]) }}"
                               class="btn btn-sm btn-outline-secondary student-suggestion-chip">
                                {{ $suggestion->name }}
                                @if($suggestion->class)
                                    <span class="text-muted">· {{ $suggestion->class->name }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
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
                                <td><strong style="color: #fd7e14;">{{ $student->identifier ?? 'N/A' }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-title rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background-color: rgba(253, 126, 20, 0.1); color: #fd7e14;">
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
                                        <span class="badge" style="background-color: #fd7e14;">{{ $student->class->name ?? 'N/A' }}</span>
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
                                        <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-warning" style="color: #fd7e14; border-color: #fd7e14;" data-bs-toggle="tooltip" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteStudentModal{{ $student->id }}" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Modal de confirmation de suppression -->
                                    <div class="modal fade" id="deleteStudentModal{{ $student->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <p>Êtes-vous sûr de vouloir supprimer l'élève <strong>{{ $student->name }}</strong> ?</p>
                                                    <p class="text-danger"><i class="fas fa-warning me-1"></i>Cette action est irréversible.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                    <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-trash me-1"></i>Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4 mb-3 d-flex justify-content-center" id="pagination-container">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>

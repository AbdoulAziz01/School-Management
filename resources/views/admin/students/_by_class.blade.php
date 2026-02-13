<div class="row mb-3">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
            <input type="text" class="form-control" id="searchClass" placeholder="Rechercher une classe...">
        </div>
    </div>
    <div class="col-md-6 text-end">
        <span class="badge fs-6" style="background-color: #fd7e14;">{{ $classes->count() }} classes</span>
        <span class="badge bg-success fs-6 ms-2">{{ $classes->sum('students_count') }} élèves affectés</span>
    </div>
</div>

<div class="accordion" id="classesAccordion">
    @foreach($classes->sortBy('name') as $class)
        @php
            $classStudents = $studentsByClass->get($class->id, collect());
            $hasStudents = $classStudents->count() > 0;
        @endphp
        <div class="accordion-item class-item" data-class-name="{{ strtolower($class->name) }}">
            <h2 class="accordion-header" id="heading{{ $class->id }}">
                <button class="accordion-button {{ !$hasStudents ? 'collapsed' : '' }}" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#collapse{{ $class->id }}" 
                        aria-expanded="{{ $hasStudents ? 'true' : 'false' }}" 
                        aria-controls="collapse{{ $class->id }}">
                    <div class="d-flex align-items-center justify-content-between w-100 me-3">
                        <div>
                            <i class="fas fa-chalkboard-teacher me-2" style="color: #fd7e14;"></i>
                            <strong>{{ $class->name }}</strong>
                            @if($class->level)
                                <small class="text-muted ms-2">({{ $class->level->name }})</small>
                            @endif
                        </div>
                        <div>
                            <span class="badge {{ $classStudents->count() > 0 ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                {{ $classStudents->count() }} élève(s)
                            </span>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapse{{ $class->id }}" 
                 class="accordion-collapse collapse {{ $hasStudents && $loop->first ? 'show' : '' }}" 
                 aria-labelledby="heading{{ $class->id }}" 
                 data-bs-parent="#classesAccordion">
                <div class="accordion-body p-0">
                    @if($classStudents->isEmpty())
                        <div class="alert alert-info m-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i>Aucun élève dans cette classe.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 120px;">Identifiant</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th style="width: 100px;">Statut</th>
                                        <th style="width: 150px;" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classStudents->sortBy('name') as $student)
                                        <tr>
                                            <td>
                                                <span class="badge" style="background-color: #fd7e14;">{{ $student->identifier ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <div class="avatar-title bg-{{ $student->status === 'approved' ? 'success' : 'warning' }} bg-opacity-10 text-{{ $student->status === 'approved' ? 'success' : 'warning' }} rounded-circle" 
                                                             style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                                        </div>
                                                    </div>
                                                    <span>{{ $student->name }}</span>
                                                </div>
                                            </td>
                                            <td><small class="text-muted">{{ $student->email }}</small></td>
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
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.students.show', $student) }}" 
                                                       class="btn" style="color: #fd7e14; border-color: #fd7e14;" 
                                                       title="Voir détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.students.edit', $student) }}" 
                                                       class="btn btn-outline-secondary" 
                                                       title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.students.unassign', $student) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Retirer {{ $student->name }} de cette classe ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Retirer de la classe">
                                                            <i class="fas fa-user-minus"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($classes->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>Aucune classe n'a été créée pour le moment.
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchClass');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const classItems = document.querySelectorAll('.class-item');
            
            classItems.forEach(function(item) {
                const className = item.getAttribute('data-class-name');
                if (className.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script>

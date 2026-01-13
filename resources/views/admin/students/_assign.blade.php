<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Affectation des élèves aux classes</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.students.assign.bulk') }}" method="POST" class="mb-4">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="class_id" class="form-label">Sélectionnez une classe</label>
                    <div class="class-select-wrapper">
                        <select name="class_id" id="class_id" class="form-select" required>
                            <option value="">-- Sélectionnez une classe --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">
                                    {{ $class->name }} - {{ $class->academicYear->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer l'affectation
                    </button>
                </div>
            </div>
            
            <div class="mt-4">
                <h6>Sélectionnez les élèves à affecter :</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50"><input type="checkbox" id="select-all"></th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Classe actuelle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="students[]" value="{{ $student->id }}" class="student-checkbox">
                                    </td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->email }}</td>
                                    <td>
                                        @if($student->class)
                                            <span class="badge bg-primary">{{ $student->class->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">Non affecté</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Aucun étudiant trouvé</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Gestion de la sélection/désélection de tous les étudiants
    document.getElementById('select-all').addEventListener('change', function(e) {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
    });
    
    // Désélectionner "Sélectionner tout" si une case est décochée
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!this.checked) {
                document.getElementById('select-all').checked = false;
            } else {
                // Vérifier si toutes les cases sont cochées
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                document.getElementById('select-all').checked = allChecked;
            }
        });
    });
    
    // Trier les options de classe
    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('class_id');
        if (classSelect) {
            const options = Array.from(classSelect.options);
            const selectedValue = classSelect.value;
            
            // Trier les options par niveau (6ème à Terminale) et par nom
            options.sort((a, b) => {
                if (a.value === '') return -1;
                if (b.value === '') return 1;
                
                const aText = a.text.toLowerCase();
                const bText = b.text.toLowerCase();
                
                // Extraire le niveau de la classe (6e, 5e, 4e, 3e, 2nde, 1ère, Tle)
                const getLevel = (text) => {
                    if (text.includes('6e')) return 0;
                    if (text.includes('5e')) return 1;
                    if (text.includes('4e')) return 2;
                    if (text.includes('3e')) return 3;
                    if (text.includes('2nde')) return 4;
                    if (text.includes('1ère') || text.includes('1ere')) return 5;
                    if (text.includes('tle') || text.includes('term')) return 6;
                    return 7; // Autres niveaux à la fin
                };
                
                const aLevel = getLevel(aText);
                const bLevel = getLevel(bText);
                
                if (aLevel !== bLevel) {
                    return aLevel - bLevel;
                }
                
                // Si même niveau, trier par nom de classe
                return aText.localeCompare(bText);
            });
            
            // Vider et réinsérer les options triées
            while (classSelect.options.length > 0) {
                classSelect.remove(0);
            }
            
            options.forEach(option => {
                classSelect.add(option);
            });
            
            // Restaurer la valeur sélectionnée
            classSelect.value = selectedValue;
        }
    });
</script>
@endpush

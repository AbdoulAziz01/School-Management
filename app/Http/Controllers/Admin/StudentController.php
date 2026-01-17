<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Affiche la liste des étudiants
     */
    /**
     * Affiche la page de gestion des élèves avec onglets
     */
    /**
     * Affiche la liste de tous les étudiants (tous statuts confondus)
     */
    public function index()
    {
        try {
            // Récupérer TOUS les étudiants (tous statuts)
            $students = User::with(['class', 'class.academicYear', 'class.level'])
                ->whereIn('role', ['student', 'eleve'])
                ->orderBy('name')
                ->paginate(20);
                
            // Récupérer les étudiants non affectés pour l'onglet d'affectation
            $unassignedStudents = User::whereIn('role', ['student', 'eleve'])
                ->where('status', 'approved')
                ->whereNull('class_id')
                ->orderBy('name')
                ->get();

            // Récupérer toutes les classes avec leurs relations
            $classes = SchoolClass::with(['level', 'academicYear'])
                ->orderBy('name')
                ->get();

            // Si c'est une requête AJAX, on retourne uniquement le contenu de l'onglet
            if (request()->ajax()) {
                $view = request('tab', 'list') === 'assignment' ? 'admin.students._assign' : 'admin.students._list';
                return [
                    'html' => view($view, [
                        'students' => $view === 'admin.students._assign' ? $unassignedStudents : $students,
                        'classes' => $classes
                    ])->render(),
                    'pagination' => $students->links()->toHtml()
                ];
            }

            // Journalisation pour le débogage
            \Log::info('Liste des étudiants', [
                'total' => $students->total(),
                'unassigned' => $unassignedStudents->count(),
                'current_page' => $students->currentPage(),
                'per_page' => $students->perPage()
            ]);

            // Retourner la vue complète avec les onglets
            return view('admin.students.index', [
                'students' => $students,
                'unassignedStudents' => $unassignedStudents,
                'classes' => $classes,
                'active_tab' => 'list'
            ]);

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'error' => 'Une erreur est survenue : ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Affiche le formulaire d'affectation des élèves aux classes
     */
    public function showAssignForm()
    {
        // Récupérer les élèves approuvés sans classe avec pagination
        $unassignedStudents = User::whereIn('role', ['student', 'eleve'])
            ->where('status', User::STATUS_APPROVED)
            ->whereNull('class_id')
            ->orderBy('name')
            ->paginate(15, ['*'], 'unassigned_page');
            
        // Récupérer les élèves déjà affectés à une classe avec pagination
        $assignedStudents = User::whereIn('role', ['student', 'eleve'])
            ->where('status', User::STATUS_APPROVED)
            ->whereNotNull('class_id')
            ->with('class')
            ->orderBy('name')
            ->paginate(15, ['*'], 'assigned_page');
        
        // Récupérer toutes les classes avec leurs relations
        $classes = SchoolClass::with(['level', 'academicYear'])
            ->orderBy('name')
            ->get();
        
        // Journalisation pour le débogage
        \Log::info('Données de la page d\'affectation:', [
            'unassigned' => $unassignedStudents->count(),
            'assigned' => $assignedStudents->count(),
            'classes' => $classes->count()
        ]);
            
        return view('admin.students.assign', compact('unassignedStudents', 'assignedStudents', 'classes'));
    }

    /**
     * Traite l'affectation des élèves aux classes (en masse)
     */
    public function assignToClassBulk(Request $request)
    {
        try {
            $validated = $request->validate([
                'class_id' => 'required|exists:classes,id',
                'students' => 'required|array',
                'students.*' => 'exists:users,id'
            ]);

            DB::beginTransaction();

            // Mettre à jour la classe pour les étudiants sélectionnés
            User::whereIn('id', $validated['students'])
                ->update([
                    'class_id' => $validated['class_id'],
                    'status' => 'approved' // Mettre à jour le statut lors de l'affectation
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Les élèves ont été affectés à la classe avec succès.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'affectation : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Affecte un élève à une classe
     */
    public function assignToClass(Request $request, User $student)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $student->update([
                'class_id' => $request->class_id,
                'status' => 'approved' // Mettre à jour le statut lors de l'affectation
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Élève affecté avec succès à la classe',
                'student' => $student->load('class')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'affectation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche le formulaire de création d'un étudiant
     */
    public function create()
    {
        $classes = SchoolClass::with('academicYear')
            ->orderBy('name')
            ->get()
            ->groupBy(function($class) {
                return $class->academicYear->name;
            });

        return view('admin.students.create', compact('classes'));
    }

    /**
     * Enregistre un nouvel étudiant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'date_of_birth' => 'required|date|before:today',
            'class_id' => 'nullable|exists:classes,id',
            'status' => ['required', 'string', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        try {
            DB::beginTransaction();

            // Générer un identifiant unique
            $lastStudent = User::where('role', 'eleve')
                ->orderBy('id', 'desc')
                ->first();

            $studentNumber = $lastStudent ? (int)substr($lastStudent->identifier, 1) + 1 : 1;
            $identifier = 'E' . str_pad($studentNumber, 5, '0', STR_PAD_LEFT);

            $student = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'identifier' => $identifier,
                'password' => bcrypt('password'), // Mot de passe par défaut
                'role' => 'eleve',
                'status' => $validated['status'],
                'date_of_birth' => $validated['date_of_birth'],
                'class_id' => $validated['class_id'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.students.show', $student)
                ->with('success', 'Étudiant créé avec succès. Identifiant: ' . $identifier);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la création d\'un élève : ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'élève : ' . $e->getMessage());
        }
    }


    /**
     * Affiche le formulaire de modification d'un étudiant
     */
    public function edit(User $student)
    {
        if ($student->role !== 'eleve') {
            abort(404);
        }

        $classes = SchoolClass::with('academicYear')
            ->orderBy('name')
            ->get()
            ->groupBy(function($class) {
                return $class->academicYear->name;
            });

        return view('admin.students.edit', compact('student', 'classes'));
    }

    /**
     * Met à jour un étudiant
     */
    public function update(Request $request, User $student)
    {
        if ($student->role !== 'eleve') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($student->id)
            ],
            'date_of_birth' => 'required|date|before:today',
            'class_id' => 'nullable|exists:classes,id',
            'status' => ['required', 'string', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        try {
            DB::beginTransaction();

            $student->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
                'date_of_birth' => $validated['date_of_birth'],
                'class_id' => $validated['class_id'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.students.show', $student)
                ->with('success', 'Étudiant mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour de l\'étudiant.');
        }
    }

    /**
     * Supprime un étudiant
     */
    public function destroy(User $student)
    {
        if ($student->role !== 'eleve') {
            abort(404);
        }

        try {
            DB::beginTransaction();
            
            // Supprimer les relations avant de supprimer l'étudiant
            $student->class_id = null;
            $student->save();
            
            $student->delete();
            
            DB::commit();

            return redirect()
                ->route('admin.students.index')
                ->with('success', 'Élève supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Une erreur est survenue lors de la suppression de l\'élève : ' . $e->getMessage());
        }
    }

    /**
     * Affiche le formulaire de réinitialisation du mot de passe
     */
    public function showResetPasswordForm(User $student)
    {
        if ($student->role !== 'eleve') {
            abort(404);
        }

        return view('admin.students.reset-password', compact('student'));
    }

    /**
     * Réinitialise le mot de passe d'un étudiant
     */
    public function resetPassword(Request $request, User $student)
    {
        if ($student->role !== 'eleve') {
            abort(404);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $student->update([
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()
            ->route('admin.students.show', $student)
            ->with('success', 'Mot de passe réinitialisé avec succès.');
    }

    /**
     * Affiche la liste des étudiants en attente d'approbation
     */
public function pending()
{
    $students = User::where('status', 'pending')
        ->where('role', 'student')
        ->with('class')
        ->orderBy('created_at', 'desc')
        ->paginate(10); // Ajout de la pagination avec 10 éléments par page

    return view('admin.students.pending', compact('students'));
}

    /**
     * Approuver un étudiant
     */
    public function approve(User $student)
    {
        if ($student->role !== 'eleve' || $student->status !== 'pending') {
            return back()->with('error', 'Action non autorisée.');
        }

        $student->update(['status' => 'approved']);

        return back()->with('success', 'Étudiant approuvé avec succès.');
    }

    /**
     * Affecter automatiquement un étudiant à une classe (méthode interne)
     */
    protected function autoAssignToClass(User $student)
    {
        // Récupérer l'année scolaire en cours
        $academicYear = AcademicYear::where('is_current', true)->first();
        
        if (!$academicYear) {
            throw new \Exception("Aucune année scolaire n'est définie comme année en cours.");
        }

        // Trouver une classe du niveau de l'étudiant avec de la place disponible
        $class = SchoolClass::where('level_id', $student->level_id)
            ->where('academic_year_id', $academicYear->id)
            ->withCount(['students' => function($query) {
                $query->where('status', User::STATUS_APPROVED);
            }])
            ->having('students_count', '<', DB::raw('capacity'))
            ->first();

        // Si aucune classe n'a de place, créer une nouvelle classe
        if (!$class) {
            $level = Level::findOrFail($student->level_id);
            $classCount = SchoolClass::where('level_id', $student->level_id)
                ->where('academic_year_id', $academicYear->id)
                ->count();

            $class = SchoolClass::create([
                'name' => $level->name . ' ' . ($classCount + 1),
                'level_id' => $student->level_id,
                'academic_year_id' => $academicYear->id,
                'capacity' => 30, // Capacité par défaut
            ]);
        }

        // Affecter l'étudiant à la classe
        $student->update(['class_id' => $class->id]);

        return $class;
    }

    /**
     * Rejeter une inscription d'étudiant
     */
    public function reject(User $student)
    {
        if ($student->role !== User::ROLE_STUDENT || !$student->isPending()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $student->update(['status' => User::STATUS_REJECTED]);
        
        // Ici, vous pourriez ajouter une notification par email
        // $student->notify(new StudentRejected());

        return back()->with('success', 'Inscription de l\'étudiant rejetée.');
    }

    /**
     * Affiche les détails d'un étudiant
     */
    public function show(User $student)
    {
        if (!in_array($student->role, ['student', 'eleve'])) {
            abort(404, 'Utilisateur non trouvé ou n\'est pas un étudiant');
        }

        // Charger uniquement les relations existantes et nécessaires
        $student->load([
            'class', 
            'class.academicYear', 
            'class.level'
        ]);

        return view('admin.students.show', [
            'student' => $student,
            'schoolClass' => $student->class // Alias pour la compatibilité avec la vue
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->input('search', ''));

            // Récupérer TOUS les étudiants (tous statuts)
            $studentsQuery = User::with(['class', 'class.academicYear', 'class.level'])
                ->whereIn('role', ['student', 'eleve']);

            if ($search !== '') {
                $term = '%' . addcslashes($search, '%_\\') . '%';
                $studentsQuery->where(function ($query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('identifier', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            }

            $students = $studentsQuery->orderBy('name')->paginate(20)->withQueryString();
                
            // Récupérer les étudiants non affectés pour l'onglet d'affectation (tous statuts)
            $unassignedStudents = User::whereIn('role', ['student', 'eleve'])
                ->whereNull('class_id')
                ->orderBy('name')
                ->get();

            // Récupérer toutes les classes avec leurs relations et le nombre d'élèves
            $classes = SchoolClass::with(['level', 'academicYear'])
                ->withCount(['students' => function($query) {
                    $query->whereIn('role', ['student', 'eleve']);
                }])
                ->orderBy('name')
                ->get();

            // Grouper les étudiants par classe pour l'onglet "Par classe"
            $studentsByClass = User::with(['class', 'class.level'])
                ->whereIn('role', ['student', 'eleve'])
                ->whereNotNull('class_id')
                ->orderBy('name')
                ->get()
                ->groupBy('class_id');

            // Si c'est une requête AJAX, on retourne uniquement le contenu de l'onglet
            if (request()->ajax()) {
                $view = request('tab', 'list') === 'assignment' ? 'admin.students._assign' : 'admin.students._list';
                return [
                    'html' => view($view, [
                        'students' => $view === 'admin.students._assign' ? $unassignedStudents : $students,
                        'classes' => $classes,
                        'search' => $search,
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
            $activeTab = request('tab', 'list');
            if ($search !== '') {
                $activeTab = 'list';
            }

            return view('admin.students.index', [
                'students' => $students,
                'unassignedStudents' => $unassignedStudents,
                'classes' => $classes,
                'studentsByClass' => $studentsByClass,
                'active_tab' => $activeTab,
                'search' => $search,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur affichage liste élèves', ['error' => $e->getMessage()]);

            if (request()->ajax()) {
                return response()->json(['error' => 'Une erreur est survenue.'], 500);
            }
            return redirect()->back()->with('error', 'Une erreur est survenue.');
        }
    }

    /**
     * Affiche le formulaire d'affectation des élèves aux classes
     */
    public function showAssignForm()
    {
        // Récupérer les élèves sans classe avec pagination (tous statuts)
        $unassignedStudents = User::whereIn('role', ['student', 'eleve'])
            ->whereNull('class_id')
            ->orderBy('name')
            ->paginate(15, ['*'], 'unassigned_page');
            
        // Récupérer les élèves déjà affectés à une classe avec pagination (tous statuts)
        $assignedStudents = User::whereIn('role', ['student', 'eleve'])
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
                'class_id'   => 'required|exists:classes,id',
                'students'   => 'required|array',
                'students.*' => [
                    'integer',
                    Rule::exists('users', 'id')->where(
                        fn ($q) => $q->whereIn('role', User::ROLE_STUDENT_ALIASES)
                    ),
                ],
            ]);

            DB::beginTransaction();

            // Garde-fou : on n'affecte QUE des élèves (jamais admin/prof par erreur)
            $affected = User::whereIn('id', $validated['students'])
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->update([
                    'class_id' => $validated['class_id'],
                    'status'   => User::STATUS_APPROVED,
                ]);

            DB::commit();

            $message = $affected > 1
                ? "Les {$affected} élèves ont été affectés à la classe avec succès."
                : "L'élève a été affecté à la classe avec succès.";

            return redirect()->route('admin.students.index', ['tab' => 'assignment'])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur affectation en masse', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erreur lors de l\'affectation.');
        }
    }
    
    /**
     * Traite l'affectation d'un élève à une classe via formulaire
     */
    public function storeAssignment(Request $request)
    {
        $request->validate([
            'student_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($q) => $q->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ),
            ],
            'class_id'   => 'required|exists:classes,id',
        ]);

        try {
            DB::beginTransaction();

            $student = User::whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->findOrFail($request->student_id);

            $student->update([
                'class_id' => $request->class_id,
                'status'   => User::STATUS_APPROVED,
            ]);

            DB::commit();

            return redirect()->route('admin.students.assign')
                ->with('success', 'Élève affecté avec succès à la classe.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur storeAssignment', ['error' => $e->getMessage()]);
            return redirect()->route('admin.students.assign')
                ->with('error', 'Erreur lors de l\'affectation.');
        }
    }

    /**
     * Affecte un élève à une classe (API JSON)
     */
    public function assignToClass(Request $request, User $student)
    {
        // Garde-fou : empêcher l'affectation d'un non-élève (admin/prof)
        abort_unless(
            in_array($student->role, User::ROLE_STUDENT_ALIASES, true),
            404,
            'Utilisateur introuvable ou n\'est pas un élève.'
        );

        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        try {
            DB::beginTransaction();

            $student->update([
                'class_id' => $request->class_id,
                'status'   => User::STATUS_APPROVED,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Élève affecté avec succès à la classe',
                'student' => $student->load('class'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur assignToClass', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'affectation.',
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
                ->with('success', 'Élève créé avec succès. Identifiant: ' . $identifier);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création d\'un élève : ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'élève.');
        }
    }


    /**
     * Affiche le formulaire de modification d'un étudiant
     */
    public function edit(User $student)
    {
        abort_unless($student->isStudent(), 404);

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
        abort_unless($student->isStudent(), 404);

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
                ->with('success', 'Élève mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour de l\'élève.');
        }
    }

    /**
     * Supprime un étudiant
     */
    public function destroy(User $student)
    {
        abort_unless($student->isStudent(), 404);

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
            Log::error('Erreur suppression élève', [
                'student_id' => $student->id,
                'error'      => $e->getMessage(),
            ]);
            return back()->with('error', 'Une erreur est survenue lors de la suppression de l\'élève.');
        }
    }

    /**
     * Affiche le formulaire de réinitialisation du mot de passe
     */
    public function showResetPasswordForm(User $student)
    {
        abort_unless($student->isStudent(), 404);

        return view('admin.students.reset-password', compact('student'));
    }

    /**
     * Réinitialise le mot de passe d'un étudiant
     */
    public function resetPassword(Request $request, User $student)
    {
        abort_unless($student->isStudent(), 404);

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
    $students = User::where('status', User::STATUS_PENDING)
        ->whereIn('role', User::ROLE_STUDENT_ALIASES)
        ->with('class')
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    return view('admin.students.pending', compact('students'));
}

    /**
     * Approuver un étudiant
     */
    public function approve(User $student)
    {
        if (! $student->isStudent() || ! $student->isPending()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $student->update(['status' => User::STATUS_APPROVED]);

        return back()->with('success', 'Élève approuvé avec succès.');
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
        if (! $student->isStudent() || ! $student->isPending()) {
            return back()->with('error', 'Action non autorisée.');
        }

        $student->update(['status' => User::STATUS_REJECTED]);

        return back()->with('success', 'Inscription de l\'élève rejetée.');
    }

    /**
     * Affiche les détails d'un étudiant
     */
    public function show(User $student)
    {
        abort_unless($student->isStudent(), 404, 'Utilisateur non trouvé ou n\'est pas un étudiant');

        // Charger les relations
        $student->load([
            'class', 
            'class.academicYear', 
            'class.level',
            'grades',
            'grades.subject'
        ]);

        // Récupérer les notes groupées par matière
        $gradesBySubject = $student->grades->groupBy('subject.name')->map(function ($subjectGrades) {
            $avg = $subjectGrades->avg('grade');
            return [
                'subject' => $subjectGrades->first()->subject->name ?? 'Inconnu',
                'coefficient' => $subjectGrades->first()->subject->coefficient ?? 1,
                'grades' => $subjectGrades->sortByDesc('date'),
                'average' => round($avg, 2),
                'count' => $subjectGrades->count()
            ];
        });

        // Calculer la moyenne générale pondérée
        $totalCoef = $gradesBySubject->sum('coefficient');
        $weightedSum = $gradesBySubject->sum(fn($g) => $g['average'] * $g['coefficient']);
        $generalAverage = $totalCoef > 0 ? round($weightedSum / $totalCoef, 2) : 0;

        // Récupérer les absences
        $attendances = \App\Models\Attendance::where('user_id', $student->id)
            ->with(['subject', 'teacher'])
            ->orderByDesc('date')
            ->get();

        // Statistiques d'absences
        $attendanceStats = [
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'excused' => $attendances->where('status', 'excused')->count(),
            'justified' => $attendances->where('justified', true)->count(),
        ];

        // Taux de présence
        $attendanceStats['presence_rate'] = $attendanceStats['total'] > 0 
            ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100, 1) 
            : 100;

        return view('admin.students.show', [
            'student' => $student,
            'schoolClass' => $student->class,
            'gradesBySubject' => $gradesBySubject,
            'generalAverage' => $generalAverage,
            'attendances' => $attendances,
            'attendanceStats' => $attendanceStats
        ]);
    }
}

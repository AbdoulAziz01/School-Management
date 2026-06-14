<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StudentCredentialsMail;
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\ClosedAcademicYearGuard;
use App\Support\SchoolUserIdentifier;
use App\Support\StudentSearch;
use App\Support\StudentGradeEvolution;
use App\Support\FormationLmdSettings;
use App\Support\FormationModuleGradeCalculator;
use App\Support\SenegalGradeSequence;
use App\Support\TenantSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

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

            $studentsQuery = StudentSearch::baseQuery()
                ->with(['class', 'class.academicYear', 'class.level']);

            StudentSearch::apply($studentsQuery, $search);

            $students = $studentsQuery->orderBy('name')->paginate(20)->withQueryString();

            $searchSuggestions = $search !== '' && $students->total() === 0
                ? StudentSearch::suggestions($search, 8)
                : collect();
                
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
                ->orderedByLevel()
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
                'searchSuggestions' => $searchSuggestions,
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
     * Suggestions de recherche (autocomplétion).
     */
    public function searchSuggestions(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $suggestions = StudentSearch::suggestions($q, 8)->map(fn (User $student) => [
            'id'         => $student->id,
            'name'       => $student->name,
            'identifier' => $student->identifier,
            'class'      => $student->class?->name,
            'url'        => route('admin.students.show', $student),
            'search_url' => route('admin.students.index', ['search' => $student->name]),
        ]);

        return response()->json($suggestions);
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
            ->orderedByLevel()
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

            $targetClass = SchoolClass::with('academicYear')->findOrFail($validated['class_id']);
            if ($response = ClosedAcademicYearGuard::denyClassMutation($targetClass)) {
                return $response;
            }

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

        $targetClass = SchoolClass::with('academicYear')->findOrFail($request->class_id);
        if ($response = ClosedAcademicYearGuard::denyClassMutation($targetClass)) {
            return $response;
        }

        try {
            DB::beginTransaction();

            $student = User::whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->findOrFail($request->student_id);

            $student->forceFill([
                'class_id' => $request->class_id,
                'status'   => User::STATUS_APPROVED,
            ])->save();

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

        $targetClass = SchoolClass::with('academicYear')->findOrFail($request->class_id);
        if (ClosedAcademicYearGuard::isClassLocked($targetClass)) {
            return response()->json([
                'success' => false,
                'message' => "L'année scolaire {$targetClass->academicYear->name} est terminée — affectation impossible.",
            ], 403);
        }

        try {
            DB::beginTransaction();

            $student->forceFill([
                'class_id' => $request->class_id,
                'status'   => User::STATUS_APPROVED,
            ])->save();

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
     * Retire un élève de sa classe.
     */
    public function unassign(User $student)
    {
        abort_unless(
            in_array($student->role, User::ROLE_STUDENT_ALIASES, true),
            404,
            'Utilisateur introuvable ou n\'est pas un élève.'
        );

        if ($student->class_id) {
            $currentClass = SchoolClass::with('academicYear')->find($student->class_id);
            if ($currentClass && ($response = ClosedAcademicYearGuard::denyClassMutation($currentClass))) {
                return $response;
            }
        }

        $student->update(['class_id' => null]);

        return redirect()->back()->with('success', 'Élève retiré de sa classe avec succès.');
    }

    /**
     * Affiche le formulaire de création d'un étudiant
     */
    public function create()
    {
        $classes = SchoolClass::with(['academicYear', 'level'])
            ->orderedByLevel()
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
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')],
            'date_of_birth' => 'required|date|before:today',
            'class_id' => 'nullable|exists:classes,id',
            'status' => ['required', 'string', Rule::in(['pending', 'approved', 'rejected'])],
            'photo' => ['nullable', 'image', 'max:4096'],
            'birth_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:8192'],
        ]);

        try {
            DB::beginTransaction();

            $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;

            if (! $schoolId) {
                throw new \RuntimeException('Établissement introuvable pour la génération de l\'identifiant.');
            }

            $identifier = SchoolUserIdentifier::next($schoolId, 'E');
            $plainPassword = Str::password(10, symbols: false);

            $fullName = trim($validated['first_name'].' '.$validated['last_name']);

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('students/photos', 'public');
            }

            $birthCertPath = null;
            if ($request->hasFile('birth_certificate')) {
                $birthCertPath = $request->file('birth_certificate')->store('students/birth_certificates', 'public');
            }

            $student = (new User)->forceFill([
                'name'       => $fullName,
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'email' => $validated['email'] ?? null,
                'identifier' => $identifier,
                'password' => Hash::make($plainPassword),
                'role' => User::ROLE_STUDENT,
                'status' => $validated['status'],
                'date_of_birth' => $validated['date_of_birth'],
                'class_id' => $validated['class_id'] ?? null,
                'school_id' => $schoolId,
                'profile_photo_path' => $photoPath,
                'birth_certificate_path' => $birthCertPath,
            ]);
            $student->save();

            $student->setAdminVisiblePassword($plainPassword);

            DB::commit();

            return redirect()
                ->route('admin.students.show', $student)
                ->with('success', 'Élève créé avec succès. Identifiants affichés sur la fiche.');

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

        $student->load(['school']);

        $classes = SchoolClass::with(['academicYear', 'level'])
            ->orderedByLevel()
            ->get()
            ->groupBy(function($class) {
                return $class->academicYear->name;
            });

        $pendingCredentials = $this->resolveStudentCredentials($student);
        $canViewPassword = auth()->user()?->canViewUserPasswords() ?? false;
        $schoolCode = $student->school?->code;

        return view('admin.students.edit', compact('student', 'classes', 'pendingCredentials', 'canViewPassword', 'schoolCode'));
    }

    /**
     * Met à jour un étudiant
     */
    public function update(Request $request, User $student)
    {
        abort_unless($student->isStudent(), 404);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($student->id),
            ],
            'date_of_birth' => 'required|date|before:today',
            'class_id' => 'nullable|exists:classes,id',
            'status' => ['required', 'string', Rule::in(['pending', 'approved', 'rejected'])],
            'photo' => ['nullable', 'image', 'max:4096'],
            'birth_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:8192'],
        ]);

        try {
            DB::beginTransaction();

            $fullName = trim($validated['first_name'].' '.$validated['last_name']);

            $updates = [
                'name'       => $fullName,
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'email' => $validated['email'] ?? null,
                'status' => $validated['status'],
                'date_of_birth' => $validated['date_of_birth'],
                'class_id' => $validated['class_id'] ?? null,
            ];

            if ($request->hasFile('photo')) {
                if ($student->profile_photo_path) {
                    \Storage::disk('public')->delete($student->profile_photo_path);
                }
                $updates['profile_photo_path'] = $request->file('photo')->store('students/photos', 'public');
            }

            if ($request->hasFile('birth_certificate')) {
                if ($student->birth_certificate_path) {
                    \Storage::disk('public')->delete($student->birth_certificate_path);
                }
                $updates['birth_certificate_path'] = $request->file('birth_certificate')->store('students/birth_certificates', 'public');
            }

            $student->forceFill($updates)->save();

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

        $student->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();
        $student->setAdminVisiblePassword($validated['password']);

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

        $student->forceFill(['status' => User::STATUS_APPROVED])->save();

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
     * Envoie identifiant + mot de passe à l'élève par email.
     */
    public function sendCredentials(User $student)
    {
        abort_unless($student->isStudent(), 404);
        $this->assertCanManageStudentCredentials();

        $plainPassword = $student->adminVisiblePassword() ?? $this->assignStudentPassword($student);
        $result = $this->sendStudentCredentials($student, $plainPassword);

        if ($result === true) {
            return back()->with('success', 'Identifiants envoyés à '.$student->email.'.');
        }

        return back()->with('error', is_string($result) ? $result : 'Impossible d\'envoyer l\'email.');
    }

    /**
     * Génère un nouveau mot de passe consultable par l'admin.
     */
    public function regenerateCredentials(User $student)
    {
        abort_unless($student->isStudent(), 404);
        $this->assertCanManageStudentCredentials();

        $this->assignStudentPassword($student);

        return back()->with('success', 'Nouveau mot de passe généré. Il reste visible sur cette fiche.');
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
            'grades.subject',
            'school',
        ]);

        // Colonnes d'évaluation (S1·D1 → S2·Compo)
        $evaluationColumns = [];
        foreach ([1, 2] as $semester) {
            foreach (SenegalGradeSequence::ORDER as $type) {
                $evaluationColumns[] = [
                    'key'    => 's'.$semester.'_'.$type,
                    'header' => 'S'.$semester.' · '.match ($type) {
                        'devoir1'     => 'D1',
                        'devoir2'     => 'D2',
                        'composition' => 'Compo',
                        default       => $type,
                    },
                ];
            }
        }

        $useLmdGrading = (bool) $student->school?->usesLmdGrading();
        $lmdSettings = FormationLmdSettings::fromSchool($student->school);
        $passingGradeMin = $useLmdGrading
            ? $lmdSettings->passingGradeMin
            : (float) config('school.passing_grade_min', 10);

        $classYearId = $student->class?->academic_year_id;
        $gradesForDisplay = $classYearId
            ? $student->grades->where('academic_year_id', $classYearId)
            : $student->grades;

        // Notes groupées par matière avec une note par colonne d'évaluation
        $gradesBySubject = $gradesForDisplay->groupBy('subject.name')->map(function ($subjectGrades) use ($useLmdGrading, $lmdSettings, $passingGradeMin) {
            $official = $subjectGrades
                ->whereIn('type', SenegalGradeSequence::ORDER)
                ->whereIn('semester', [1, 2]);

            $subjectModel = $subjectGrades->first()?->subject;
            $moduleSettings = ($useLmdGrading && $subjectModel)
                ? FormationLmdSettings::fromSubject($subjectModel)
                : $lmdSettings;
            $modulePassingMin = $moduleSettings->passingGradeMin;

            $slots = [];
            foreach ([1, 2] as $semester) {
                foreach (SenegalGradeSequence::ORDER as $type) {
                    $grade = $official->first(
                        fn ($g) => (int) $g->semester === $semester && $g->type === $type
                    );
                    $slots['s'.$semester.'_'.$type] = $grade !== null ? (float) $grade->grade : null;
                }
            }

            if ($useLmdGrading) {
                $summary = FormationModuleGradeCalculator::summarize($official, $moduleSettings);
                $average = $summary['average'];
                $lmdValidated = $summary['validated'];
                $lmdMode = $summary['mode'];
            } else {
                $average = $official->isNotEmpty()
                    ? round((float) $official->avg('grade'), 2)
                    : null;
                $lmdValidated = $average !== null && $average >= $passingGradeMin;
                $lmdMode = null;
            }

            return [
                'subject'     => $subjectGrades->first()->subject->name ?? 'Inconnu',
                'coefficient' => $subjectGrades->first()->subject->coefficient ?? 1,
                'slots'       => $slots,
                'average'     => $average,
                'lmd_validated' => $lmdValidated,
                'lmd_mode'    => FormationModuleGradeCalculator::modeLabel($lmdMode, $moduleSettings),
                'lmd_formula' => $useLmdGrading ? $moduleSettings->shortLabel() : null,
                'passing_min' => $useLmdGrading ? $modulePassingMin : $passingGradeMin,
            ];
        })->sortKeys();

        // Moyenne générale pondérée
        $gradedSubjects = $gradesBySubject->filter(fn ($g) => $g['average'] !== null);
        $totalCoef = $gradedSubjects->sum('coefficient');
        $weightedSum = $gradedSubjects->sum(fn ($g) => $g['average'] * $g['coefficient']);
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

        $academicYearId = $student->class?->academic_year_id;
        $gradeEvolutionJson = StudentGradeEvolution::chartData($student, $academicYearId);

        $pendingCredentials = $this->resolveStudentCredentials($student);
        $canViewPassword = auth()->user()?->canViewUserPasswords() ?? false;
        $schoolCode = $student->school?->code;

        return view('admin.students.show', [
            'student' => $student,
            'schoolClass' => $student->class,
            'gradesBySubject' => $gradesBySubject,
            'generalAverage' => $generalAverage,
            'attendances' => $attendances,
            'attendanceStats' => $attendanceStats,
            'gradeEvolutionJson' => $gradeEvolutionJson,
            'evaluationColumns'  => $evaluationColumns,
            'pendingCredentials' => $pendingCredentials,
            'canViewPassword' => $canViewPassword,
            'schoolCode' => $schoolCode,
            'useLmdGrading' => $useLmdGrading,
            'lmdFormulaLabel' => $useLmdGrading ? 'Pondération CC / Examen définie par module' : null,
            'passingGradeMin' => $passingGradeMin,
        ]);
    }

    private function assertCanManageStudentCredentials(): void
    {
        abort_unless(auth()->user()?->canViewUserPasswords(), 403, 'Accès réservé à l\'administrateur.');
    }

    /** @return array{name: string, identifier: string|null, email: string|null, password: string}|null */
    private function resolveStudentCredentials(User $student): ?array
    {
        if (! auth()->user()?->canViewUserPasswords()) {
            return null;
        }

        $plainPassword = $student->adminVisiblePassword();

        if ($plainPassword === null && Hash::check('password', $student->password)) {
            $student->setAdminVisiblePassword('password');
            $plainPassword = 'password';
        }

        if ($plainPassword === null) {
            return null;
        }

        return [
            'name' => $student->name,
            'identifier' => $student->identifier,
            'email' => $student->email,
            'password' => $plainPassword,
        ];
    }

    private function assignStudentPassword(User $student): string
    {
        $plainPassword = Str::password(10, symbols: false);

        $student->forceFill([
            'password' => Hash::make($plainPassword),
        ])->save();

        $student->setAdminVisiblePassword($plainPassword);

        return $plainPassword;
    }

    /** @return bool|string */
    private function sendStudentCredentials(User $student, ?string $plainPassword = null): bool|string
    {
        if (empty($student->email)) {
            return 'Cet élève n\'a pas d\'adresse email.';
        }

        if (empty($student->identifier)) {
            return 'Cet élève n\'a pas d\'identifiant de connexion.';
        }

        if (config('mail.default') === 'log') {
            return 'MAIL_MAILER=log : aucun email réel n\'est envoyé. Mettez MAIL_MAILER=smtp dans .env puis php artisan config:clear.';
        }

        if (empty(config('mail.mailers.smtp.password')) && config('mail.default') === 'smtp') {
            return 'Configuration incomplète : renseignez MAIL_PASSWORD dans .env.';
        }

        $plainPassword ??= $this->assignStudentPassword($student);

        try {
            $student->loadMissing('school');
            Mail::to($student->email)->send(new StudentCredentialsMail($student, $plainPassword));

            $student->forceFill([
                'password' => Hash::make($plainPassword),
                'invitation_email_sent_at' => now(),
            ])->save();

            $student->setAdminVisiblePassword($plainPassword);

            return true;
        } catch (TransportExceptionInterface $e) {
            Log::error('Erreur SMTP envoi identifiants élève', [
                'student_id' => $student->id,
                'email' => $student->email,
                'message' => $e->getMessage(),
            ]);

            return 'Erreur d\'envoi email : '.$e->getMessage();
        } catch (\Throwable $e) {
            Log::error('Erreur envoi identifiants élève', [
                'student_id' => $student->id,
                'message' => $e->getMessage(),
            ]);

            return 'Erreur d\'envoi email : '.$e->getMessage();
        }
    }
}

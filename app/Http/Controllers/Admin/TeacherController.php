<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\TeacherCredentialService;
use App\Support\SchoolSubjectProvisioner;
use App\Support\SchoolUserIdentifier;
use App\Support\TenantSchool;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeacherController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private TeacherCredentialService $credentials,
        private \App\Services\TeacherTeachingService $teaching
    ) {}

    /**
     * Affiche la liste des enseignants
     */
    public function index(Request $request)
    {
        $query = User::whereIn('role', User::ROLE_TEACHER_ALIASES)
            ->with([
                'assignedClasses' => fn ($q) => $q->whereHas('academicYear', fn ($ay) => $ay->where('is_current', true)),
                'teacherAssignments' => fn ($q) => $q->active()->whereHas('academicYear', fn ($ay) => $ay->where('is_current', true)),
                'teacherAssignments.schoolClass',
            ]);

        // Recherche par nom, email ou identifiant
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('identifier', 'ilike', "%{$search}%");
            });
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par classe(s) — un enseignant est retenu s'il est titulaire
        // (primaire, assignedClasses) ou affecté matière+classe (collège/lycée,
        // teacherAssignments) sur au moins une des classes cochées.
        $selectedClassIds = array_map('intval', (array) $request->input('class_ids', []));
        if (! empty($selectedClassIds)) {
            $query->where(function ($q) use ($selectedClassIds) {
                $q->whereHas('assignedClasses', fn ($aq) => $aq->whereIn('classes.id', $selectedClassIds))
                    ->orWhereHas('teacherAssignments', fn ($aq) => $aq->active()->whereIn('class_id', $selectedClassIds));
            });
        }

        $teachers = $query->orderBy('name')->paginate(15)->withQueryString();

        $filterClasses = SchoolClass::with('level')
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->orderBy('name')
            ->get();

        return view('admin.teachers.index', compact('teachers', 'filterClasses', 'selectedClassIds'));
    }

    /**
     * Affiche le formulaire de création d'un enseignant
     */
    public function create()
    {
        return view('admin.teachers.create', $this->teachingFormData());
    }

    /**
     * Enregistre un nouvel enseignant
     */
    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id ?? TenantSchool::id();
        $primaireSubjectIds = $this->primaireSubjectIdsFor($schoolId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'subjects' => $primaireSubjectIds === null ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'subjects.*' => ['integer', 'exists:subjects,id'],
            'classes' => ['required', 'array', 'min:1'],
            'classes.*' => ['integer', 'exists:classes,id'],
        ], [
            'subjects.required' => 'Sélectionnez au moins une matière.',
            'subjects.min' => 'Sélectionnez au moins une matière.',
            'classes.required' => 'Sélectionnez au moins une classe.',
            'classes.min' => 'Sélectionnez au moins une classe.',
        ]);

        try {
            DB::beginTransaction();

            if (! $schoolId) {
                throw new \RuntimeException('Établissement introuvable pour la génération de l\'identifiant.');
            }

            $identifier = SchoolUserIdentifier::next($schoolId, 'P');

            $teacher = (new User)->forceFill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'identifier' => $identifier,
                'password' => Hash::make(Str::password(32)),
                'role' => User::ROLE_TEACHER,
                'status' => User::STATUS_APPROVED,
                'school_id' => auth()->user()->school_id,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'email_verified_at' => now(),
            ]);
            $teacher->save();

            $this->teaching->sync(
                $teacher,
                $primaireSubjectIds ?? $request->input('subjects', []),
                $request->input('classes', [])
            );

            DB::commit();

            $plainPassword = $this->credentials->assignPassword($teacher);

            if ($this->credentials->emailEnabled()) {
                $mailResult = $this->credentials->send($teacher, $plainPassword);

                if ($mailResult === true) {
                    return redirect()
                        ->route('admin.teachers.show', $teacher)
                        ->with('success', 'Enseignant créé. Identifiants affichés ci-dessous et envoyés à '.$teacher->email.'.');
                }

                return redirect()
                    ->route('admin.teachers.show', $teacher)
                    ->with('error', is_string($mailResult) ? $mailResult : 'Enseignant créé, mais l\'envoi email a échoué.');
            }

            return redirect()
                ->route('admin.teachers.show', $teacher)
                ->with('success', 'Enseignant créé. Communiquez les identifiants affichés sur cette fiche.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création enseignant', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création de l\'enseignant.');
        }
    }

    /**
     * Affiche les détails d'un enseignant
     */
    public function show($id)
    {
        $teacher = User::whereIn('role', User::ROLE_TEACHER_ALIASES)
            ->with([
                'assignedClasses.level', 'assignedClasses.academicYear', 'assignedClasses.students', 'subjects',
                'teacherAssignments' => fn ($q) => $q->active(),
                'teacherAssignments.schoolClass.level',
                'teacherAssignments.schoolClass.academicYear',
                'teacherAssignments.schoolClass.students',
            ])
            ->findOrFail($id);

        $pendingCredentials = $this->credentials->resolvePending($teacher);
        $canViewPassword = auth()->user()?->canViewUserPasswords() ?? false;

        return view('admin.teachers.show', compact('teacher', 'pendingCredentials', 'canViewPassword'));
    }

    /**
     * Affiche le formulaire de modification d'un enseignant
     */
    public function edit(User $teacher)
    {
        // Garde-fou : la cible doit bien être un enseignant
        abort_unless($teacher->isTeacher(), 404, 'Enseignant introuvable.');

        $teacher->load(['subjects', 'assignedClasses']);

        $pendingCredentials = $this->credentials->resolvePending($teacher);
        $canViewPassword = auth()->user()?->canViewUserPasswords() ?? false;

        return view('admin.teachers.edit', array_merge(
            compact('teacher', 'pendingCredentials', 'canViewPassword'),
            $this->teachingFormData(),
            [
                'selectedSubjectIds' => old('subjects', $teacher->subjects->pluck('id')->all()),
                'selectedClassIds' => old('classes', $teacher->assignedClasses->pluck('id')->all()),
            ]
        ));
    }

    /**
     * Met à jour les informations d'un enseignant
     */
    public function update(Request $request, User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404, 'Enseignant introuvable.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($teacher->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,approved,rejected'],
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['integer', 'exists:subjects,id'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['integer', 'exists:classes,id'],
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'status' => $validated['status'],
            ];

            $teacher->update($updateData);

            $primaireSubjectIds = $this->primaireSubjectIdsFor($teacher->school_id);

            $this->teaching->sync(
                $teacher,
                $primaireSubjectIds ?? $request->input('subjects', []),
                $request->input('classes', [])
            );

            DB::commit();

            $teacher->refresh();

            $redirect = redirect()
                ->route('admin.teachers.edit', $teacher)
                ->with('success', 'Les informations de l\'enseignant ont été mises à jour avec succès.');

            if ($request->boolean('send_invitation_email') && $this->credentials->emailEnabled()) {
                $plainPassword = $this->credentials->assignPassword($teacher);
                $mailResult = $this->credentials->send($teacher, $plainPassword);

                if ($mailResult === true) {
                    return $redirect->with(
                        'success',
                        'Enregistré. Identifiant et mot de passe temporaire envoyés à '.$teacher->email.'.'
                    );
                }

                return $redirect->with(
                    'error',
                    is_string($mailResult) ? $mailResult : 'Enregistré, mais l\'email n\'a pas pu être envoyé.'
                );
            }

            return $redirect;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour enseignant', [
                'teacher_id' => $teacher->id,
                'error'      => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour.');
        }
    }

    /**
     * Renvoie l'email d'invitation (si activé) ou affiche les identifiants à l'écran.
     */
    public function sendInvitation(User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404, 'Enseignant introuvable.');
        $this->credentials->assertCanManage();

        $plainPassword = $this->credentials->assignPassword($teacher);

        $result = $this->credentials->send($teacher, $plainPassword, manual: true);

        if ($result === true) {
            return back()->with(
                'success',
                'Identifiant et mot de passe temporaire envoyés à '.$teacher->email.'.'
            );
        }

        return back()->with('error', is_string($result) ? $result : 'Impossible d\'envoyer l\'email d\'invitation.');
    }

    /**
     * Génère un nouveau mot de passe consultable par l'admin.
     */
    public function regenerateCredentials(User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404, 'Enseignant introuvable.');
        $this->credentials->assertCanManage();

        $this->credentials->assignPassword($teacher);

        return back()->with('success', 'Nouveau mot de passe généré. Il reste visible sur cette fiche.');
    }

    /**
     * Supprime un enseignant
     */
    public function destroy(User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404, 'Enseignant introuvable.');

        try {
            if ($teacher->teacherAssignments()->exists()) {
                return back()
                    ->with('error', 'Impossible de supprimer cet enseignant car il a des affectations en cours.');
            }

            $teacher->delete();

            return redirect()
                ->route('admin.teachers.index')
                ->with('success', 'L\'enseignant a été supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur suppression enseignant', [
                'teacher_id' => $teacher->id,
                'error'      => $e->getMessage(),
            ]);
            return back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }

    /**
     * @return array{subjects: \Illuminate\Support\Collection, classes: \Illuminate\Support\Collection}
     */
    private function teachingFormData(): array
    {
        $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;

        SchoolSubjectProvisioner::ensureForSchool($schoolId);

        $isPrimaire = School::find($schoolId)?->isPrimaireEstablishment() ?? false;

        $subjectsQuery = Subject::orderBy('name');

        // École primaire pure : la liste "Matières enseignées" ne doit
        // proposer que le catalogue officiel du primaire, jamais les
        // matières collège/lycée. Les écoles Mixte gardent le catalogue
        // complet (un enseignant peut y intervenir sur les deux cycles).
        // Une matière désactivée pour tous les niveaux primaire (grille
        // "Notation primaire") — ex. une école qui n'enseigne pas
        // l'Éducation Religieuse — n'est plus proposée du tout.
        if ($isPrimaire) {
            $subjectsQuery->whereHas('levels', fn ($q) => $q->where('levels.cycle', 'primaire')->where('level_subject.is_active', true));
        }

        $subjects = $subjectsQuery->get();

        $classes = SchoolClass::with(['level', 'academicYear'])
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->orderBy('name')
            ->get();

        return compact('subjects', 'classes', 'isPrimaire');
    }

    /**
     * Au primaire, le maître titulaire enseigne l'intégralité du programme :
     * il n'y a pas de sélection de matières à faire, on assigne tout le
     * catalogue automatiquement plutôt que de fier ce choix à l'admin.
     *
     * @return list<int>|null null si l'établissement n'est pas primaire (pas d'auto-assignation).
     */
    private function primaireSubjectIdsFor(?int $schoolId): ?array
    {
        if (! School::find($schoolId)?->isPrimaireEstablishment()) {
            return null;
        }

        return Subject::where('school_id', $schoolId)
            ->whereHas('levels', fn ($q) => $q->where('levels.cycle', 'primaire')->where('level_subject.is_active', true))
            ->pluck('id')
            ->all();
    }
}

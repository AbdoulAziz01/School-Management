<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TeacherCredentialsMail;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Support\SchoolSubjectProvisioner;
use App\Support\SchoolUserIdentifier;
use App\Support\TenantSchool;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeacherController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Affiche la liste des enseignants
     */
    public function index(Request $request)
    {
        $query = User::whereIn('role', User::ROLE_TEACHER_ALIASES);

        // Recherche par nom, email ou identifiant
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('identifier', 'like', "%{$search}%");
            });
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $teachers = $query->orderBy('name')->paginate(15);

        return view('admin.teachers.index', compact('teachers'));
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'subjects' => ['required', 'array', 'min:1'],
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

            $schoolId = auth()->user()->school_id ?? TenantSchool::id();

            if (! $schoolId) {
                throw new \RuntimeException('Établissement introuvable pour la génération de l\'identifiant.');
            }

            $identifier = SchoolUserIdentifier::next($schoolId, 'P');

            $teacher = User::withoutGlobalScopes()->create([
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

            $this->syncTeacherTeaching(
                $teacher,
                $request->input('subjects', []),
                $request->input('classes', [])
            );

            DB::commit();

            $plainPassword = $this->assignTeacherPassword($teacher);

            if ($this->teacherCredentialsEmailEnabled()) {
                $mailResult = $this->sendTeacherCredentials($teacher, $plainPassword);

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
            ->with(['assignedClasses.level', 'assignedClasses.academicYear', 'assignedClasses.students', 'subjects'])
            ->findOrFail($id);

        $pendingCredentials = $this->resolveTeacherCredentials($teacher);
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

        $pendingCredentials = $this->resolveTeacherCredentials($teacher);
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

            $this->syncTeacherTeaching(
                $teacher,
                $request->input('subjects', []),
                $request->input('classes', [])
            );

            DB::commit();

            $teacher->refresh();

            $redirect = redirect()
                ->route('admin.teachers.edit', $teacher)
                ->with('success', 'Les informations de l\'enseignant ont été mises à jour avec succès.');

            if ($request->boolean('send_invitation_email') && $this->teacherCredentialsEmailEnabled()) {
                $plainPassword = $this->assignTeacherPassword($teacher);
                $mailResult = $this->sendTeacherCredentials($teacher, $plainPassword);

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
        $this->assertCanManageTeacherCredentials();

        $plainPassword = $teacher->adminVisiblePassword() ?? $this->assignTeacherPassword($teacher);

        $result = $this->sendTeacherCredentials($teacher, $plainPassword, manual: true);

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
        $this->assertCanManageTeacherCredentials();

        $this->assignTeacherPassword($teacher);

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

    private function teacherCredentialsEmailEnabled(): bool
    {
        return (bool) config('mail.teacher_credentials_email_enabled', false);
    }

    /** @return array{name: string, identifier: string|null, email: string, password: string} */
    private function teacherCredentialsFlash(User $teacher, string $plainPassword): array
    {
        return [
            'name' => $teacher->name,
            'identifier' => $teacher->identifier,
            'email' => $teacher->email,
            'password' => $plainPassword,
        ];
    }

    /** @return array{name: string, identifier: string|null, email: string, password: string}|null */
    private function resolveTeacherCredentials(User $teacher): ?array
    {
        if (! auth()->user()?->canViewUserPasswords()) {
            return null;
        }

        $plainPassword = $teacher->adminVisiblePassword();

        if ($plainPassword === null) {
            return null;
        }

        return $this->teacherCredentialsFlash($teacher, $plainPassword);
    }

    private function assertCanManageTeacherCredentials(): void
    {
        abort_unless(auth()->user()?->canViewUserPasswords(), 403, 'Accès réservé à l\'administrateur.');
    }

    private function assignTeacherPassword(User $teacher): string
    {
        $plainPassword = Str::password(10, symbols: false);

        $teacher->forceFill([
            'password' => Hash::make($plainPassword),
        ])->save();

        $teacher->setAdminVisiblePassword($plainPassword);

        return $plainPassword;
    }

    /**
     * Envoie identifiant + mot de passe par email (si activé dans la config).
     *
     * @return bool|string true si envoyé, string = message d'erreur
     */
    private function sendTeacherCredentials(User $teacher, ?string $plainPassword = null, bool $manual = false): bool|string
    {
        if (! $manual && ! $this->teacherCredentialsEmailEnabled()) {
            return 'Envoi email désactivé (TEACHER_SEND_CREDENTIALS_EMAIL=false).';
        }

        if (empty($teacher->email)) {
            return 'Cet enseignant n\'a pas d\'adresse email.';
        }

        if (empty($teacher->identifier)) {
            return 'Cet enseignant n\'a pas d\'identifiant de connexion.';
        }

        if (config('mail.default') === 'log') {
            return 'MAIL_MAILER=log : aucun email réel n\'est envoyé. Mettez MAIL_MAILER=smtp dans .env puis php artisan config:clear.';
        }

        if (empty(config('mail.mailers.smtp.password')) && config('mail.default') === 'smtp') {
            return 'Configuration incomplète : renseignez MAIL_PASSWORD et MAIL_FROM_ADDRESS dans .env, puis php artisan config:clear.';
        }

        $plainPassword ??= Str::password(10, symbols: false);

        try {
            Mail::to($teacher->email)->send(new TeacherCredentialsMail($teacher, $plainPassword));

            $teacher->forceFill([
                'password' => Hash::make($plainPassword),
                'invitation_email_sent_at' => now(),
            ])->save();

            $teacher->setAdminVisiblePassword($plainPassword);

            return true;
        } catch (TransportExceptionInterface $e) {
            Log::error('Erreur SMTP envoi identifiants enseignant', [
                'teacher_id' => $teacher->id,
                'email' => $teacher->email,
                'message' => $e->getMessage(),
            ]);

            if (str_contains($e->getMessage(), '535') || str_contains($e->getMessage(), 'Authentication failed')) {
                return 'Échec connexion Brevo (535) : vérifiez MAIL_PASSWORD (clé xsmtpsib-...) dans .env.';
            }

            if (str_contains($e->getMessage(), '525') || str_contains($e->getMessage(), 'Unauthorized IP')) {
                return 'Brevo bloque votre IP (525) : autorisez votre IP dans Brevo → Sécurité → IPs autorisées.';
            }

            return 'Erreur d\'envoi email : '.$e->getMessage();
        } catch (\Throwable $e) {
            Log::error('Erreur envoi identifiants enseignant', [
                'teacher_id' => $teacher->id,
                'email' => $teacher->email,
                'message' => $e->getMessage(),
            ]);

            return 'Erreur d\'envoi email : '.$e->getMessage();
        }
    }

    /**
     * @return array{subjects: \Illuminate\Support\Collection, classes: \Illuminate\Support\Collection}
     */
    private function teachingFormData(): array
    {
        SchoolSubjectProvisioner::ensureForSchool(TenantSchool::id() ?? auth()->user()?->school_id);

        $subjects = Subject::orderBy('name')->get();

        $classes = SchoolClass::with(['level', 'academicYear'])
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->orderBy('name')
            ->get();

        return compact('subjects', 'classes');
    }

    private function syncTeacherTeaching(User $teacher, array $subjectIds, array $classIds): void
    {
        $subjectIds = array_values(array_unique(array_map('intval', $subjectIds)));
        $classIds = array_values(array_unique(array_map('intval', $classIds)));

        $teacher->subjects()->sync($subjectIds);
        $teacher->assignedClasses()->sync($classIds);

        $currentYear = AcademicYear::where('is_current', true)->first();

        if (! $currentYear) {
            return;
        }

        TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $currentYear->id)
            ->delete();

        if ($subjectIds === [] || $classIds === []) {
            return;
        }

        $classes = SchoolClass::whereIn('id', $classIds)->get();

        foreach ($classes as $class) {
            foreach ($subjectIds as $subjectId) {
                TeacherAssignment::firstOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'class_id' => $class->id,
                        'subject_id' => $subjectId,
                        'academic_year_id' => $class->academic_year_id ?? $currentYear->id,
                    ],
                    ['school_id' => $teacher->school_id]
                );
            }
        }
    }
}

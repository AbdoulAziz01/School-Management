<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Support\SenegalGradeSequence;
use App\Models\User;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use App\Services\StudentClassPromotionService;
use App\Models\AcademicYear;
use App\Support\ClosedAcademicYearGuard;
use App\Support\DashboardAcademicYearContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherGradesController extends Controller
{
    /** @return array<string, mixed> */
    private function yearContext(Request $request): array
    {
        $selectedYear = DashboardAcademicYearContext::resolve($request, 'teacher');
        $currentYear = AcademicYear::where('is_current', true)->first();

        return [
            'selectedYear' => $selectedYear,
            'currentYear' => $currentYear,
            'academicYears' => DashboardAcademicYearContext::allYears(),
            'isSelectedYearCurrent' => $selectedYear && $currentYear
                && (int) $selectedYear->id === (int) $currentYear->id,
            'gradesLocked' => ClosedAcademicYearGuard::areGradesLocked($selectedYear),
            'gradesLockedMessage' => ClosedAcademicYearGuard::gradesLockedMessage($selectedYear),
        ];
    }

    /**
     * Contexte d'écriture (année courante ouverte uniquement).
     *
     * @return array{currentYear: ?AcademicYear, gradesLocked: bool, gradesLockedMessage: string}
     */
    private function gradesContext(): array
    {
        $currentYear = AcademicYear::where('is_current', true)->first();

        return [
            'currentYear' => $currentYear,
            'gradesLocked' => ClosedAcademicYearGuard::areGradesLocked($currentYear),
            'gradesLockedMessage' => ClosedAcademicYearGuard::gradesLockedMessage($currentYear),
        ];
    }

    /**
     * Afficher les notes par classe/matière
     */
    public function index(Request $request)
    {
        $teacher = Auth::user();
        $yearCtx = $this->yearContext($request);
        $selectedYear = $yearCtx['selectedYear'];
        $academicYears = $yearCtx['academicYears'];
        $isSelectedYearCurrent = $yearCtx['isSelectedYearCurrent'];
        $gradesLocked = $yearCtx['gradesLocked'];
        $gradesLockedMessage = $yearCtx['gradesLockedMessage'];
        
        $classes = $teacher->assignedClasses()
            ->with('level')
            ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
            ->get();
        
        $subjects = $teacher->subjects()->get();

        $assignments = TeacherAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
            ->get();

        // Fusionner les matières
        $subjects = $subjects->merge($assignments->pluck('subject')->filter())->unique('id');
        
        // Filtres
        $selectedClassId = $request->get('class_id');
        $selectedSubjectId = $request->get('subject_id');
        
        $grades = collect();
        $students = collect();
        
        if ($selectedClassId && $selectedSubjectId) {
            if (! $this->hasClassAccess($teacher, (int) $selectedClassId, $selectedYear)) {
                $students = collect();
                $grades = collect();
            } elseif (! $this->teachesClassSubject($teacher, (int) $selectedClassId, (int) $selectedSubjectId, $selectedYear)) {
                $students = collect();
                $grades = collect();
            } else {
                $students = User::where('class_id', $selectedClassId)
                    ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                    ->where('status', User::STATUS_APPROVED)
                    ->orderBy('name')
                    ->get();

                $grades = Grade::where('subject_id', $selectedSubjectId)
                    ->whereIn('user_id', $students->pluck('id'))
                    ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
                    ->orderBy('date', 'desc')
                    ->get()
                    ->groupBy('user_id');
            }
        }
        
        $evaluationColumns = [];
        foreach ([1, 2] as $semester) {
            foreach (SenegalGradeSequence::ORDER as $type) {
                $evaluationColumns[] = [
                    'semester' => $semester,
                    'type' => $type,
                    'header' => 'S'.$semester.' · '.match ($type) {
                        'devoir1' => 'D1',
                        'devoir2' => 'D2',
                        'composition' => 'Compo',
                        default => $type,
                    },
                    'label' => 'Semestre '.$semester.' — '.SenegalGradeSequence::LABELS[$type],
                ];
            }
        }

        return view('teacher.grades.index', compact(
            'classes',
            'subjects',
            'grades',
            'students',
            'selectedClassId',
            'selectedSubjectId',
            'evaluationColumns',
            'gradesLocked',
            'gradesLockedMessage',
            'selectedYear',
            'academicYears',
            'isSelectedYearCurrent',
        ));
    }

    /**
     * Formulaire de saisie des notes
     */
    public function create(Request $request)
    {
        $context = $this->gradesContext();
        if ($context['gradesLocked'] || ! $context['currentYear']) {
            return redirect()
                ->route('teacher.grades.index')
                ->with('error', $context['gradesLockedMessage'] ?: 'Aucune année scolaire active — saisie impossible.');
        }

        $teacher = Auth::user();
        $currentYear = $context['currentYear'];

        $classes  = $teacher->assignedClasses()
            ->with(['level', 'academicYear'])
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true)->where('is_closed', false))
            ->get();
        $subjects = $teacher->subjects()->get();

        $selectedClassId   = $request->get('class_id');
        $selectedSubjectId = $request->get('subject_id');
        $students          = collect();

        if ($selectedClassId) {
            if (! $this->hasClassAccess($teacher, (int) $selectedClassId, $currentYear)) {
                return back()->with('error', 'Vous n\'avez pas accès à cette classe.');
            }

            if ($selectedSubjectId && ! $this->teachesClassSubject($teacher, (int) $selectedClassId, (int) $selectedSubjectId, $currentYear)) {
                return back()->with('error', 'Vous n\'enseignez pas cette matière pour cette classe.');
            }

            $students = User::where('class_id', $selectedClassId)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->where('status', User::STATUS_APPROVED)
                ->orderBy('name')
                ->get();
        }

        $gradeTypes = SenegalGradeSequence::evaluationTypes();
        $nextAllowed = null;
        $evaluationProgress = null;
        $existingGradesForEvaluation = collect();

        if ($selectedClassId && $selectedSubjectId) {
            $nextAllowed = SenegalGradeSequence::nextAllowed(
                (int) $selectedClassId,
                (int) $selectedSubjectId,
                $currentYear?->id
            );
            $evaluationProgress = SenegalGradeSequence::progress(
                (int) $selectedClassId,
                (int) $selectedSubjectId,
                $currentYear?->id
            );

            if ($nextAllowed) {
                $existingGradesForEvaluation = SenegalGradeSequence::gradesForEvaluation(
                    (int) $selectedClassId,
                    (int) $selectedSubjectId,
                    $nextAllowed['semester'],
                    $nextAllowed['type'],
                    $currentYear?->id
                );
            }
        }

        return view('teacher.grades.create', compact(
            'classes',
            'subjects',
            'students',
            'selectedClassId',
            'selectedSubjectId',
            'gradeTypes',
            'nextAllowed',
            'evaluationProgress',
            'existingGradesForEvaluation',
        ));
    }

    /**
     * Enregistrer les notes
     */
    public function store(Request $request)
    {
        $context = $this->gradesContext();
        if ($context['gradesLocked'] || ! $context['currentYear']) {
            return back()->with('error', $context['gradesLockedMessage'] ?: 'Aucune année scolaire active — saisie impossible.');
        }

        $request->validate([
            'class_id'         => 'required|exists:classes,id',
            'subject_id'       => 'required|exists:subjects,id',
            'type'             => 'required|in:devoir1,devoir2,composition',
            'date'             => 'required|date',
            'coefficient'      => 'required|numeric|min:0.5|max:5',
            'semester'         => 'required|integer|in:1,2',
            'grades'           => 'required|array',
            'grades.*.user_id' => 'required|integer|exists:users,id',
            'grades.*.grade'   => 'nullable|numeric|min:0|max:20',
        ]);

        $teacher     = Auth::user();
        $currentYear = $context['currentYear'];

        if (! $this->hasClassAccess($teacher, (int) $request->class_id, $currentYear)) {
            return back()->with('error', 'Vous n\'avez pas accès à cette classe.');
        }

        $schoolClass = SchoolClass::with('academicYear')->findOrFail($request->class_id);
        if (ClosedAcademicYearGuard::isClassLocked($schoolClass)) {
            return back()->with('error', ClosedAcademicYearGuard::gradesLockedMessage($schoolClass->academicYear));
        }

        if (! $this->teachesClassSubject($teacher, (int) $request->class_id, (int) $request->subject_id, $currentYear)) {
            return back()->with('error', 'Vous n\'enseignez pas cette matière pour cette classe.');
        }

        $submittedUserIds = collect($request->grades)->pluck('user_id')->unique();
        $validStudentIds  = User::where('class_id', $request->class_id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->whereIn('id', $submittedUserIds)
            ->pluck('id')
            ->all();

        if (count($validStudentIds) !== $submittedUserIds->count()) {
            return back()->with('error', 'Certains élèves ne font pas partie de cette classe.');
        }

        $semester = (int) $request->semester;

        $sequenceError = SenegalGradeSequence::validateEntry(
            (int) $request->class_id,
            (int) $request->subject_id,
            $semester,
            $request->type,
            $currentYear?->id
        );

        if ($sequenceError !== null) {
            return back()->withInput()->with('error', $sequenceError);
        }

        DB::beginTransaction();
        try {
            $saved = 0;
            $skipped = 0;

            foreach ($request->grades as $gradeData) {
                $value = $gradeData['grade'] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }

                $userId = (int) $gradeData['user_id'];

                if (SenegalGradeSequence::findStudentGrade(
                    $userId,
                    (int) $request->subject_id,
                    $semester,
                    $request->type,
                    $currentYear?->id
                )) {
                    $skipped++;

                    continue;
                }

                Grade::create([
                    'user_id'          => $userId,
                    'subject_id'       => $request->subject_id,
                    'grade'            => $value,
                    'type'             => $request->type,
                    'date'             => $request->date,
                    'coefficient'      => $request->coefficient,
                    'semester'         => $semester,
                    'academic_year_id' => $currentYear?->id,
                    'comments'         => $gradeData['comments'] ?? null,
                    'appreciation'     => $gradeData['appreciation'] ?? null,
                ]);
                $saved++;
            }

            DB::commit();

            if ($saved === 0 && $skipped > 0) {
                return back()->with('warning', 'Aucune note enregistrée — les notes existantes ne peuvent pas être modifiées.');
            }

            if ($saved === 0) {
                return back()->with('warning', 'Aucune note à enregistrer.');
            }

            if ($currentYear) {
                $promotionService = app(StudentClassPromotionService::class);
                foreach ($validStudentIds as $studentId) {
                    $student = User::find($studentId);
                    if ($student) {
                        $promotionService->tryPromote($student, $currentYear);
                    }
                }
            }

            return redirect()->route('teacher.grades.index', [
                'class_id'   => $request->class_id,
                'subject_id' => $request->subject_id,
            ])->with('success', 'Notes enregistrées avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur enregistrement notes', [
                'teacher_id' => $teacher->id,
                'error'      => $e->getMessage(),
            ]);
            return back()->with('error', 'Erreur lors de l\'enregistrement des notes.');
        }
    }

    private function hasClassAccess(User $teacher, int $classId, ?AcademicYear $year): bool
    {
        return $teacher->assignedClasses()
            ->where('classes.id', $classId)
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->exists();
    }

    private function teachesClassSubject(User $teacher, int $classId, int $subjectId, ?AcademicYear $year): bool
    {
        if (! $this->hasClassAccess($teacher, $classId, $year)) {
            return false;
        }

        $yearId = $year?->id;

        if (TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->exists()) {
            return true;
        }

        return $teacher->subjects()->where('subjects.id', $subjectId)->exists();
    }

    /**
     * Formulaire de correction — un enseignant ne peut corriger une note
     * qu'une seule fois après sa saisie initiale (voir Grade::canStillBeEditedByTeacher()).
     */
    public function edit($id)
    {
        $grade = Grade::with(['user', 'subject'])->findOrFail($id);
        $teacher = Auth::user();

        $denyReason = $this->denyGradeEditReason($teacher, $grade);
        if ($denyReason !== null) {
            return redirect()->route('teacher.grades.index')->with('error', $denyReason);
        }

        return view('teacher.grades.edit', compact('grade'));
    }

    /**
     * Applique la correction unique et avertit l'administration par email.
     */
    public function update(Request $request, $id)
    {
        $grade = Grade::with(['user', 'subject'])->findOrFail($id);
        $teacher = Auth::user();

        $denyReason = $this->denyGradeEditReason($teacher, $grade);
        if ($denyReason !== null) {
            return redirect()->route('teacher.grades.index')->with('error', $denyReason);
        }

        $validated = $request->validate([
            'grade'        => 'required|numeric|min:0|max:20',
            'coefficient'  => 'required|numeric|min:0.5|max:5',
            'date'         => 'required|date',
            'comments'     => 'nullable|string|max:1000',
            'appreciation' => 'nullable|string|max:1000',
        ]);

        $oldGrade = (string) $grade->grade;

        DB::beginTransaction();
        try {
            $grade->update([
                'grade'              => $validated['grade'],
                'coefficient'        => $validated['coefficient'],
                'date'               => $validated['date'],
                'comments'           => $validated['comments'] ?? null,
                'appreciation'       => $validated['appreciation'] ?? null,
                'teacher_edited_at'  => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur correction note', ['grade_id' => $grade->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Erreur lors de l\'enregistrement de la correction.');
        }

        $this->notifySchoolStaffOfGradeEdit($grade, $teacher, $oldGrade, (string) $validated['grade']);

        return redirect()->route('teacher.grades.index', [
            'class_id'   => $grade->user->class_id,
            'subject_id' => $grade->subject_id,
        ])->with('success', 'Note corrigée avec succès. L\'administration a été informée de cette correction.');
    }

    /**
     * Suppression interdite pour les enseignants.
     */
    public function destroy($id)
    {
        return back()->with('error', 'Les notes enregistrées ne peuvent pas être supprimées. Contactez l\'administration.');
    }

    /**
     * @return string|null Motif de refus, ou null si l'enseignant peut corriger cette note.
     */
    private function denyGradeEditReason(User $teacher, Grade $grade): ?string
    {
        if (! $grade->canStillBeEditedByTeacher()) {
            return 'Vous avez déjà utilisé votre correction unique sur cette note. Contactez l\'administration pour toute modification supplémentaire.';
        }

        $year = $grade->academic_year_id ? AcademicYear::find($grade->academic_year_id) : null;
        if (ClosedAcademicYearGuard::areGradesLocked($year)) {
            return ClosedAcademicYearGuard::gradesLockedMessage($year);
        }

        $classId = $grade->user?->class_id;
        if (! $classId || ! $this->teachesClassSubject($teacher, (int) $classId, (int) $grade->subject_id, $year)) {
            return 'Vous n\'enseignez pas cette matière pour cette classe.';
        }

        return null;
    }

    /**
     * Avertit admins et surveillants de l'établissement par email (en file
     * d'attente) qu'une correction de note vient d'avoir lieu.
     */
    private function notifySchoolStaffOfGradeEdit(Grade $grade, User $teacher, string $oldGrade, string $newGrade): void
    {
        $recipients = User::where('school_id', $grade->school_id)
            ->whereIn('role', User::ROLE_SCHOOL_STAFF)
            ->whereNotNull('email')
            ->get();

        foreach ($recipients as $recipient) {
            \Mail::to($recipient->email)->queue(
                new \App\Mail\GradeEditedByTeacherMail($grade, $teacher, $oldGrade, $newGrade)
            );
        }
    }
}

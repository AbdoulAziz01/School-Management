<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherGradesController extends Controller
{
    /**
     * Afficher les notes par classe/matière
     */
    public function index(Request $request)
    {
        $teacher = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        // Récupérer les classes affectées via class_teacher
        $classes = $teacher->assignedClasses()->with('level')->get();
        
        // Récupérer les matières du professeur
        $subjects = $teacher->subjects;
        
        // Aussi récupérer les affectations TeacherAssignment si disponibles
        $assignments = TeacherAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->get();
        
        // Fusionner les matières
        $subjects = $subjects->merge($assignments->pluck('subject')->filter())->unique('id');
        
        // Filtres
        $selectedClassId = $request->get('class_id');
        $selectedSubjectId = $request->get('subject_id');
        
        $grades = collect();
        $students = collect();
        
        if ($selectedClassId && $selectedSubjectId) {
            // Vérifier que l'enseignant a accès à cette classe
            $hasAccess = $teacher->assignedClasses()->where('classes.id', $selectedClassId)->exists();
            
            if ($hasAccess) {
                // Récupérer les élèves de la classe
                $students = User::where('class_id', $selectedClassId)
                    ->whereIn('role', ['student', 'eleve'])
                    ->where('status', 'approved')
                    ->orderBy('name')
                    ->get();
                
                // Récupérer les notes
                $grades = Grade::where('subject_id', $selectedSubjectId)
                    ->whereIn('user_id', $students->pluck('id'))
                    ->orderBy('date', 'desc')
                    ->get()
                    ->groupBy('user_id');
            }
        }
        
        return view('teacher.grades.index', compact(
            'classes',
            'subjects',
            'grades',
            'students',
            'selectedClassId',
            'selectedSubjectId'
        ));
    }

    /**
     * Formulaire de saisie des notes
     */
    public function create(Request $request)
    {
        $teacher = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();

        $classes  = $teacher->assignedClasses()->with('level')->get();
        $subjects = $teacher->subjects;

        $selectedClassId   = $request->get('class_id');
        $selectedSubjectId = $request->get('subject_id');
        $students          = collect();

        if ($selectedClassId) {
            // Garde-fou IDOR : le prof doit être affecté à cette classe
            $hasAccess = $teacher->assignedClasses()
                ->where('classes.id', $selectedClassId)
                ->exists();

            if (! $hasAccess) {
                return back()->with('error', 'Vous n\'avez pas accès à cette classe.');
            }

            $students = User::where('class_id', $selectedClassId)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->where('status', User::STATUS_APPROVED)
                ->orderBy('name')
                ->get();
        }

        $gradeTypes = [
            'devoir'   => 'Devoir',
            'controle' => 'Contrôle',
            'examen'   => 'Examen',
            'oral'     => 'Oral',
            'tp'       => 'Travaux Pratiques',
            'projet'   => 'Projet',
        ];

        return view('teacher.grades.create', compact(
            'classes',
            'subjects',
            'students',
            'selectedClassId',
            'selectedSubjectId',
            'gradeTypes'
        ));
    }

    /**
     * Enregistrer les notes
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id'         => 'required|exists:classes,id',
            'subject_id'       => 'required|exists:subjects,id',
            'type'             => 'required|string',
            'date'             => 'required|date',
            'coefficient'      => 'required|numeric|min:0.5|max:5',
            'semester'         => 'nullable|integer|in:1,2',
            'grades'           => 'required|array',
            'grades.*.user_id' => 'required|integer|exists:users,id',
            'grades.*.grade'   => 'nullable|numeric|min:0|max:20',
        ]);

        $teacher     = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();

        // 1. Le prof doit être affecté à cette classe
        $hasClassAccess = $teacher->assignedClasses()
            ->where('classes.id', $request->class_id)
            ->exists();

        if (! $hasClassAccess) {
            return back()->with('error', 'Vous n\'avez pas accès à cette classe.');
        }

        // 2. Le prof doit enseigner cette matière (via subjects ou TeacherAssignment)
        $teachesSubject = $teacher->subjects()
                ->where('subjects.id', $request->subject_id)
                ->exists()
            || TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('subject_id', $request->subject_id)
                ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
                ->exists();

        if (! $teachesSubject) {
            return back()->with('error', 'Vous n\'enseignez pas cette matière.');
        }

        // 3. Tous les user_id soumis doivent appartenir à la classe ciblée et être des élèves approuvés
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

        $semester = $request->input('semester', $this->guessCurrentSemester());

        DB::beginTransaction();
        try {
            foreach ($request->grades as $gradeData) {
                $value = $gradeData['grade'] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }

                Grade::create([
                    'user_id'          => (int) $gradeData['user_id'],
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
            }

            DB::commit();

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

    /**
     * Heuristique simple pour déterminer le semestre courant
     * (système sénégalais : S1 oct-jan, S2 fév-juin).
     */
    private function guessCurrentSemester(): int
    {
        $month = now()->month;
        return ($month >= 10 || $month <= 1) ? 1 : 2;
    }

    /**
     * Modifier une note
     */
    public function edit($id)
    {
        $grade = Grade::with(['user', 'subject'])->findOrFail($id);
        
        // Vérifier l'accès
        $teacher = Auth::user();
        $hasAccess = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('class_id', $grade->user->class_id)
            ->where('subject_id', $grade->subject_id)
            ->exists();
        
        if (!$hasAccess) {
            return back()->with('error', 'Vous n\'avez pas accès à cette note.');
        }
        
        $gradeTypes = [
            'devoir' => 'Devoir',
            'controle' => 'Contrôle',
            'examen' => 'Examen',
            'oral' => 'Oral',
            'tp' => 'Travaux Pratiques',
            'projet' => 'Projet'
        ];
        
        return view('teacher.grades.edit', compact('grade', 'gradeTypes'));
    }

    /**
     * Mettre à jour une note
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'grade' => 'required|numeric|min:0|max:20',
            'type' => 'required|string',
            'date' => 'required|date',
            'coefficient' => 'required|numeric|min:0.5|max:5',
            'comments' => 'nullable|string|max:500',
            'appreciation' => 'nullable|string|max:500'
        ]);
        
        $grade = Grade::findOrFail($id);
        
        // Vérifier l'accès
        $teacher = Auth::user();
        $hasAccess = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('class_id', $grade->user->class_id)
            ->where('subject_id', $grade->subject_id)
            ->exists();
        
        if (!$hasAccess) {
            return back()->with('error', 'Vous n\'avez pas accès à cette note.');
        }
        
        $grade->update([
            'grade' => $request->grade,
            'type' => $request->type,
            'date' => $request->date,
            'coefficient' => $request->coefficient,
            'comments' => $request->comments,
            'appreciation' => $request->appreciation,
        ]);
        
        return redirect()->route('teacher.grades.index', [
            'class_id' => $grade->user->class_id,
            'subject_id' => $grade->subject_id
        ])->with('success', 'Note mise à jour avec succès.');
    }

    /**
     * Supprimer une note
     */
    public function destroy($id)
    {
        $grade = Grade::findOrFail($id);
        
        // Vérifier l'accès
        $teacher = Auth::user();
        $hasAccess = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('class_id', $grade->user->class_id)
            ->where('subject_id', $grade->subject_id)
            ->exists();
        
        if (!$hasAccess) {
            return back()->with('error', 'Vous n\'avez pas accès à cette note.');
        }
        
        $classId = $grade->user->class_id;
        $subjectId = $grade->subject_id;
        
        $grade->delete();
        
        return redirect()->route('teacher.grades.index', [
            'class_id' => $classId,
            'subject_id' => $subjectId
        ])->with('success', 'Note supprimée avec succès.');
    }
}

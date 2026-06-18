<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Support\FormationLmdSettings;
use App\Support\FormationModuleGradeCalculator;
use App\Support\DashboardAcademicYearContext;
use App\Support\StudentClassContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Services\StudentClassPromotionService;

class StudentBulletinController extends Controller
{
    /**
     * Afficher le bulletin semestriel système sénégalais
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Récupérer le semestre demandé (1 ou 2), par défaut le semestre actuel
        $semester = $request->query('semester', $this->getCurrentSemester());

        $academicYear = DashboardAcademicYearContext::resolve($request, 'student');

        if (! $academicYear) {
            return view('student.bulletin-senegal', [
                'error' => 'Aucune année académique sélectionnée',
            ]);
        }

        $class = StudentClassContext::resolveForYear($user, $academicYear);
        $level = $class?->level;
        $serie = $level?->serie;

        // Récupérer les notes du semestre
        $grades = Grade::where('user_id', $user->id)
            ->where('semester', $semester)
            ->where('academic_year_id', $academicYear->id)
            ->with('subject')
            ->get();

        // Grouper les notes par matière et calculer les moyennes
        $bulletinData = $this->calculateBulletinData($grades, $level, $user->school);

        // Calculer la moyenne générale pondérée
        $generalAverage = $this->calculateWeightedAverage($bulletinData);

        // Calculer le rang de l'élève
        $rankData = $this->calculateRank($user, $class, $semester, $academicYear);

        // Informations élève pour le bulletin
        $studentInfo = [
            'name' => $user->name,
            'class' => StudentClassContext::labelForYear($user, $academicYear),
            'serie' => $serie,
            'identifier' => $user->identifier ?? '-',
            'academic_year' => $academicYear->name,
            'semester' => $semester,
            'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : '-',
            'level' => $level?->name ?? '-',
        ];

        // Statistiques de classe
        $classStats = $this->calculateClassStats($class, $semester, $academicYear);

        $schoolName = config('app.school_name', 'Établissement scolaire');

        // URL signée pour la vérification via QR code (validité 1 an)
        $verifyUrl = null;
        if ($class) {
            $verifyUrl = URL::signedRoute('bulletin.verify', [
                'student_id'      => $user->id,
                'class_id'        => $class->id,
                'academic_year_id' => $academicYear->id,
                'semester'        => $semester,
            ]);
        }

        return view('student.bulletin-senegal', compact(
            'bulletinData',
            'generalAverage',
            'studentInfo',
            'rankData',
            'classStats',
            'semester',
            'academicYear',
            'schoolName',
            'verifyUrl'
        ));
    }

    /**
     * Pré-récupère la table des coefficients level_subject pour un niveau donné.
     * Évite de re-requêter la BDD pour chaque matière de chaque élève.
     *
     * @return array<int, int|float> map subject_id => coefficient
     */
    private function fetchLevelCoefficients($level): array
    {
        if (! $level) {
            return [];
        }

        return DB::table('level_subject')
            ->where('level_id', $level->id)
            ->pluck('coefficient', 'subject_id')
            ->toArray();
    }

    /**
     * Calcul des données du bulletin par matière (pour UN élève).
     *
     * @param  \Illuminate\Support\Collection  $grades  Notes de l'élève (avec subject eager loaded)
     * @param  mixed  $level  Niveau (Level ou null)
     * @param  array<int,int|float>|null  $coefficients  Coefficients pré-calculés (optionnel)
     */
    private function calculateBulletinData($grades, $level, ?School $school = null, ?array $coefficients = null)
    {
        if ($school?->usesLmdGrading()) {
            return $this->calculateFormationBulletinData($grades, $school);
        }

        $coefficients ??= $this->fetchLevelCoefficients($level);

        $bulletinData    = [];
        $gradesBySubject = $grades->groupBy('subject_id');

        foreach ($gradesBySubject as $subjectId => $subjectGrades) {
            // Le subject est eager-loaded depuis l'appelant : pas de Subject::find() ici
            $subject = $subjectGrades->first()->subject ?? null;
            if (! $subject) {
                continue;
            }

            $coefficient = $coefficients[$subjectId] ?? 1;

            $devoir1     = $subjectGrades->where('type', 'devoir1')->first();
            $devoir2     = $subjectGrades->where('type', 'devoir2')->first();
            $composition = $subjectGrades->where('type', 'composition')->first();

            // Système sénégalais :
            // Moyenne devoirs = (D1 + D2) / 2
            // Moyenne matière = Moyenne devoirs * 0.4 + Composition * 0.6
            $moyenneDevoirs = null;
            $moyenneMatiere = null;

            if ($devoir1 && $devoir2) {
                $moyenneDevoirs = ($devoir1->grade + $devoir2->grade) / 2;
            }

            if ($moyenneDevoirs !== null && $composition) {
                $moyenneMatiere = ($moyenneDevoirs * 0.5) + ($composition->grade * 0.5);
            } elseif ($composition) {
                $moyenneMatiere = $composition->grade;
            } elseif ($moyenneDevoirs !== null) {
                $moyenneMatiere = $moyenneDevoirs;
            }

            $bulletinData[] = [
                'subject'         => $subject->name,
                'subject_code'    => $subject->code,
                'coefficient'     => $coefficient,
                'devoir1'         => $devoir1 ? round($devoir1->grade, 2) : null,
                'devoir2'         => $devoir2 ? round($devoir2->grade, 2) : null,
                'composition'     => $composition ? round($composition->grade, 2) : null,
                'moyenne_devoirs' => $moyenneDevoirs !== null ? round($moyenneDevoirs, 2) : null,
                'moyenne_matiere' => $moyenneMatiere !== null ? round($moyenneMatiere, 2) : null,
                'points'          => $moyenneMatiere !== null ? round($moyenneMatiere * $coefficient, 2) : null,
                'appreciation'    => $this->getAppreciation($moyenneMatiere),
            ];
        }

        usort($bulletinData, fn ($a, $b) => $b['coefficient'] <=> $a['coefficient']);

        return $bulletinData;
    }

    /**
     * Bulletin formation : moyenne module LMD (30 % CC + 70 % examen, ou 100 % si une seule famille).
     *
     * @param  \Illuminate\Support\Collection  $grades
     * @return list<array<string, mixed>>
     */
    private function calculateFormationBulletinData($grades, School $school): array
    {
        $bulletinData = [];

        foreach ($grades->groupBy('subject_id') as $subjectId => $subjectGrades) {
            $subject = $subjectGrades->first()->subject ?? null;
            if (! $subject) {
                continue;
            }

            $settings = FormationLmdSettings::fromSubject($subject);
            $coefficient = (float) ($subject->coefficient ?? 1);
            $devoir1 = $subjectGrades->where('type', 'devoir1')->first();
            $devoir2 = $subjectGrades->where('type', 'devoir2')->first();
            $composition = $subjectGrades->where('type', 'composition')->first();

            $ccGrades = $subjectGrades->filter(
                fn ($g) => in_array((string) $g->type, $settings->ccGradeTypes, true)
            );
            $moyenneDevoirs = $ccGrades->isNotEmpty()
                ? round((float) $ccGrades->avg('grade'), 2)
                : null;

            $summary = FormationModuleGradeCalculator::summarize($subjectGrades, $settings);
            $moyenneMatiere = $summary['average'];

            $bulletinData[] = [
                'subject'         => $subject->name,
                'subject_code'    => $subject->code,
                'coefficient'     => $coefficient,
                'devoir1'         => $devoir1 ? round((float) $devoir1->grade, 2) : null,
                'devoir2'         => $devoir2 ? round((float) $devoir2->grade, 2) : null,
                'composition'     => $composition ? round((float) $composition->grade, 2) : null,
                'moyenne_devoirs' => $moyenneDevoirs,
                'moyenne_exam'    => $summary['exam_average'],
                'moyenne_matiere' => $moyenneMatiere,
                'lmd_mode'        => $summary['mode'],
                'points'          => $moyenneMatiere !== null ? round($moyenneMatiere * $coefficient, 2) : null,
                'appreciation'    => $this->getAppreciation($moyenneMatiere),
                'validated'       => $summary['validated'],
            ];
        }

        usort($bulletinData, fn ($a, $b) => $b['coefficient'] <=> $a['coefficient']);

        return $bulletinData;
    }

    /**
     * Calcule en UNE requête les moyennes pondérées de tous les élèves d'une classe.
     * Remplace les boucles N+1 dans calculateRank() et calculateClassStats().
     *
     * @return array<int, float> map user_id => moyenne
     */
    private function calculateClassAverages($class, int $semester, $academicYear): array
    {
        if (! $class) {
            return [];
        }

        // 1. IDs des élèves approuvés de la classe (1 requête)
        $studentIds = User::where('class_id', $class->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return [];
        }

        // 2. Toutes les notes des élèves en UNE requête, groupées par user_id
        $gradesByStudent = Grade::whereIn('user_id', $studentIds)
            ->where('semester', $semester)
            ->where('academic_year_id', $academicYear->id)
            ->with('subject')
            ->get()
            ->groupBy('user_id');

        // 3. Coefficients level_subject pré-calculés (1 requête)
        $coefficients = $this->fetchLevelCoefficients($class->level);

        // 4. Calcul en mémoire (zéro requête supplémentaire)
        $averages = [];
        foreach ($studentIds as $studentId) {
            $studentGrades = $gradesByStudent->get($studentId, collect());
            $bulletinData  = $this->calculateBulletinData($studentGrades, $class->level, $class->school, $coefficients);
            $averages[$studentId] = $this->calculateWeightedAverage($bulletinData);
        }

        return $averages;
    }

    /**
     * Calculer la moyenne générale pondérée
     */
    private function calculateWeightedAverage($bulletinData)
    {
        $totalPoints = 0;
        $totalCoef = 0;
        
        foreach ($bulletinData as $data) {
            if ($data['moyenne_matiere'] !== null) {
                $totalPoints += $data['points'];
                $totalCoef += $data['coefficient'];
            }
        }
        
        return $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : 0;
    }

    /**
     * Calculer le rang de l'élève dans sa classe (optimisé : pas de N+1).
     */
    private function calculateRank($user, $class, $semester, $academicYear)
    {
        if (! $class) {
            return ['rank' => null, 'total' => 0];
        }

        $averages = $this->calculateClassAverages($class, $semester, $academicYear);

        if (empty($averages)) {
            return ['rank' => null, 'total' => 0];
        }

        // Tri décroissant en gardant les user_id
        arsort($averages);

        $rank  = null;
        $index = 1;
        foreach ($averages as $studentId => $avg) {
            if ((int) $studentId === (int) $user->id) {
                $rank = $index;
                break;
            }
            $index++;
        }

        return [
            'rank'  => $rank,
            'total' => count($averages),
        ];
    }

    /**
     * Calculer les statistiques de la classe (optimisé : pas de N+1).
     */
    private function calculateClassStats($class, $semester, $academicYear)
    {
        if (! $class) {
            return ['average' => null, 'highest' => null, 'lowest' => null];
        }

        $averages = array_filter(
            $this->calculateClassAverages($class, $semester, $academicYear),
            fn ($a) => $a > 0
        );

        if (empty($averages)) {
            return ['average' => null, 'highest' => null, 'lowest' => null];
        }

        return [
            'average' => round(array_sum($averages) / count($averages), 2),
            'highest' => round(max($averages), 2),
            'lowest'  => round(min($averages), 2),
        ];
    }

    /**
     * Obtenir le semestre actuel
     */
    private function getCurrentSemester()
    {
        $month = now()->month;
        // Semestre 1: Octobre à Janvier
        // Semestre 2: Février à Juin
        if ($month >= 10 || $month <= 1) return 1;
        return 2;
    }

    /**
     * Générer une appréciation basée sur la moyenne
     */
    private function getAppreciation($average)
    {
        if ($average === null) return '-';
        if ($average >= 16) return 'Très Bien';
        if ($average >= 14) return 'Bien';
        if ($average >= 12) return 'Assez Bien';
        if ($average >= 10) return 'Passable';
        if ($average >= 8) return 'Insatisfaisant';
        return 'Très Insuffisant';
    }

    /**
     * Générer le bulletin annuel
     */
    public function annual(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $academicYear = AcademicYear::where('is_current', true)->first();
        
        if (!$academicYear) {
            return view('student.bulletin-annuel', [
                'error' => 'Aucune année académique en cours'
            ]);
        }

        $class = $user->schoolClass;
        $level = $class ? $class->level : null;
        $serie = $level ? $level->serie : null;

        // Calculer les données pour les deux semestres
        $semestre1Data = $this->getSemesterData($user, 1, $academicYear, $level);
        $semestre2Data = $this->getSemesterData($user, 2, $academicYear, $level);

        // Calculer la moyenne annuelle
        $moyenneAnnuelle = 0;
        if ($semestre1Data['moyenne'] > 0 && $semestre2Data['moyenne'] > 0) {
            $moyenneAnnuelle = round(($semestre1Data['moyenne'] + $semestre2Data['moyenne']) / 2, 2);
        }

        // Décision du conseil de classe
        $decision = $this->getDecision($moyenneAnnuelle);

        $promotionResult = app(StudentClassPromotionService::class)->tryPromote($user, $academicYear);
        if (($promotionResult['promoted'] ?? false) && ($promotionResult['status'] ?? '') === 'promoted') {
            $decision['text'] = 'Admis(e) et affecté(e) en '.$user->fresh()->schoolClass?->name;
            $decision['color'] = 'success';
        } elseif (($promotionResult['status'] ?? '') === 'graduated') {
            $decision['text'] = 'Diplômé(e) — fin de scolarité';
            $decision['color'] = 'success';
        }

        $studentInfo = [
            'name' => $user->name,
            'class' => $class ? $class->name : 'Non assigné',
            'serie' => $serie,
            'identifier' => $user->identifier ?? '-',
            'academic_year' => $academicYear->name,
            'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : '-',
            'level' => $level ? $level->name : '-',
        ];

        return view('student.bulletin-annuel', compact(
            'semestre1Data',
            'semestre2Data',
            'moyenneAnnuelle',
            'decision',
            'studentInfo',
            'academicYear'
        ));
    }

    /**
     * Obtenir les données d'un semestre
     */
    private function getSemesterData($user, $semester, $academicYear, $level)
    {
        $grades = Grade::where('user_id', $user->id)
            ->where('semester', $semester)
            ->where('academic_year_id', $academicYear->id)
            ->with('subject')
            ->get();

        $bulletinData = $this->calculateBulletinData($grades, $level, $user->school);
        $moyenne = $this->calculateWeightedAverage($bulletinData);

        return [
            'data' => $bulletinData,
            'moyenne' => $moyenne
        ];
    }

    /**
     * Obtenir la décision du conseil de classe
     */
    private function getDecision($moyenneAnnuelle)
    {
        if ($moyenneAnnuelle >= 12) {
            return [
                'text' => 'Admis(e) en classe supérieure',
                'color' => 'success',
                'mention' => $this->getMention($moyenneAnnuelle)
            ];
        } elseif ($moyenneAnnuelle >= 10) {
            return [
                'text' => 'Admis(e) en classe supérieure',
                'color' => 'success',
                'mention' => null
            ];
        } elseif ($moyenneAnnuelle >= 8) {
            return [
                'text' => 'Passage conditionnel / Redoublement',
                'color' => 'warning',
                'mention' => null
            ];
        } else {
            return [
                'text' => 'Redoublement',
                'color' => 'danger',
                'mention' => null
            ];
        }
    }

    /**
     * Obtenir la mention
     */
    private function getMention($moyenne)
    {
        if ($moyenne >= 16) return 'Mention Très Bien';
        if ($moyenne >= 14) return 'Mention Bien';
        if ($moyenne >= 12) return 'Mention Assez Bien';
        return null;
    }
}

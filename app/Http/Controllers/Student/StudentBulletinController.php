<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\DashboardAcademicYearContext;
use App\Support\StudentClassContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Services\StudentClassPromotionService;
use App\Services\SchoolBot\BulletinComputation;

class StudentBulletinController extends Controller
{
    public function __construct(
        private BulletinComputation $bulletinComputation
    ) {}

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
        $semester = $request->query('semester', $this->bulletinComputation->getCurrentSemester());

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
        $bulletinData = $this->bulletinComputation->calculateBulletinData($grades, $level, $user->school);

        // Calculer la moyenne générale pondérée
        $generalAverage = $this->bulletinComputation->calculateWeightedAverage($bulletinData);

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
        $coefficients = $this->bulletinComputation->fetchLevelCoefficients($class->level);

        // 4. Calcul en mémoire (zéro requête supplémentaire)
        $averages = [];
        foreach ($studentIds as $studentId) {
            $studentGrades = $gradesByStudent->get($studentId, collect());
            $bulletinData  = $this->bulletinComputation->calculateBulletinData($studentGrades, $class->level, $class->school, $coefficients);
            $averages[$studentId] = $this->bulletinComputation->calculateWeightedAverage($bulletinData);
        }

        return $averages;
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

        $bulletinData = $this->bulletinComputation->calculateBulletinData($grades, $level, $user->school);
        $moyenne = $this->bulletinComputation->calculateWeightedAverage($bulletinData);

        return [
            'data' => $bulletinData,
            'moyenne' => $moyenne
        ];
    }

    /**
     * Obtenir la décision du conseil de classe (texte + mention délégués à
     * BulletinComputation, la couleur d'affichage reste propre à cette vue).
     */
    private function getDecision($moyenneAnnuelle)
    {
        $color = match (true) {
            $moyenneAnnuelle >= 10 => 'success',
            $moyenneAnnuelle >= 8 => 'warning',
            default => 'danger',
        };

        return [
            'text' => $this->bulletinComputation->getDecisionText($moyenneAnnuelle),
            'color' => $color,
            'mention' => $moyenneAnnuelle >= 12 ? $this->bulletinComputation->getMention($moyenneAnnuelle) : null,
        ];
    }
}

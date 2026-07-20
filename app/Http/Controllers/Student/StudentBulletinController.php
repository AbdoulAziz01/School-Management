<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\DashboardAcademicYearContext;
use App\Support\StudentClassContext;
use App\Support\Reports\BulletinSheetFormatter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Services\StudentClassPromotionService;
use App\Services\SchoolBot\BulletinComputation;

class StudentBulletinController extends Controller
{
    public function __construct(
        private BulletinComputation $bulletinComputation,
        private BulletinSheetFormatter $sheetFormatter
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

        // Le primaire n'a pas de notion de semestre (3 compositions
        // annuelles, ou 2 + examen final pour la CM2) : seul le bulletin
        // annuel est pertinent pour ce cycle.
        if ($level?->isPrimaireCycle()) {
            return redirect()->route('student.bulletin.annual');
        }

        // Récupérer les notes du semestre
        $grades = Grade::where('user_id', $user->id)
            ->where('semester', $semester)
            ->where('academic_year_id', $academicYear->id)
            ->with('subject')
            ->get();

        // Nombre d'absences sur le semestre (Attendance n'a pas de colonne
        // semestre : on découpe la période de l'année scolaire au même
        // pivot que BulletinComputation::getCurrentSemester(), fin
        // janvier / début février).
        $absenceCount = $this->countAbsences($user, $this->semesterDateRange($academicYear, $semester));

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

        // URL signée pour la vérification via QR code (validité 1 an) —
        // format SVG (pas de dépendance Imagick, contrairement au PNG).
        $verifyUrl = null;
        $qrCodeUri = null;
        if ($class) {
            $verifyUrl = URL::signedRoute('bulletin.verify', [
                'student_id'      => $user->id,
                'class_id'        => $class->id,
                'academic_year_id' => $academicYear->id,
                'semester'        => $semester,
            ]);
            $qrCodeUri = 'data:image/svg+xml;base64,'.base64_encode(
                QrCode::format('svg')->size(120)->errorCorrection('H')->generate($verifyUrl)
            );
        }

        $sheet = $this->sheetFormatter->format(
            school: $user->school,
            academicYearName: $academicYear->name,
            periodLabel: 'Semestre '.$semester,
            niveau: $level?->name ?? '-',
            classe: $studentInfo['class'],
            student: [
                'name' => $studentInfo['name'],
                'dob' => $studentInfo['date_of_birth'],
                'matricule' => $studentInfo['identifier'],
            ],
            effectif: $rankData['total'],
            rank: $rankData,
            bulletinData: $bulletinData,
            generalAverage: $generalAverage,
            maxGrade: 20.0,
            qrCodeUri: $qrCodeUri,
        );

        return view('student.bulletin-senegal', compact(
            'sheet',
            'bulletinData',
            'generalAverage',
            'studentInfo',
            'rankData',
            'classStats',
            'semester',
            'academicYear',
            'schoolName',
            'verifyUrl',
            'absenceCount'
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
        $isPrimaire = $level?->isPrimaireCycle() ?? false;

        if ($isPrimaire) {
            // Primaire : pas de semestres — une seule moyenne annuelle
            // calculée directement à partir des compositions (voir
            // App\Support\Grading). On garde la forme semestre1Data/
            // semestre2Data pour ne pas dupliquer la vue, semestre2Data
            // restant vide et masqué côté template.
            $grades = Grade::where('user_id', $user->id)
                ->where('academic_year_id', $academicYear->id)
                ->with('subject')
                ->get();

            $bulletinData = $this->bulletinComputation->calculateBulletinData($grades, $level, $user->school);
            $moyenneAnnuelle = $this->bulletinComputation->calculateWeightedAverage($bulletinData);

            $semestre1Data = ['data' => $bulletinData, 'moyenne' => $moyenneAnnuelle];
            $semestre2Data = ['data' => [], 'moyenne' => 0];
        } else {
            // Calculer les données pour les deux semestres
            $semestre1Data = $this->getSemesterData($user, 1, $academicYear, $level);
            $semestre2Data = $this->getSemesterData($user, 2, $academicYear, $level);

            // Calculer la moyenne annuelle
            $moyenneAnnuelle = 0;
            if ($semestre1Data['moyenne'] > 0 && $semestre2Data['moyenne'] > 0) {
                $moyenneAnnuelle = round(($semestre1Data['moyenne'] + $semestre2Data['moyenne']) / 2, 2);
            }
        }

        // Nombre d'absences sur l'année scolaire entière.
        $absenceCount = $this->countAbsences($user, [
            $academicYear->start_date ? Carbon::parse($academicYear->start_date) : null,
            $academicYear->end_date ? Carbon::parse($academicYear->end_date) : null,
        ]);

        // Barème de la moyenne générale : 10 en primaire (jamais /20 pour
        // ce cycle), 20 en secondaire (inchangé).
        $overallMaxGrade = $this->bulletinComputation->referenceMaxGradeForLevel($level);

        // Décision du conseil de classe
        $decision = $this->getDecision($moyenneAnnuelle, $overallMaxGrade);

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

        $studentForSheet = [
            'name' => $studentInfo['name'],
            'dob' => $studentInfo['date_of_birth'],
            'matricule' => $studentInfo['identifier'],
        ];

        // Le "look" pixel-perfect du bulletin (voir BulletinSheetFormatter)
        // n'a qu'un seul tableau de notes par feuille. Le primaire (pas de
        // semestres) tient sur une seule feuille ; le secondaire (S1 + S2)
        // produit deux feuilles imprimées à la suite, chacune avec son
        // propre classement — il n'existe pas de "classement annuel"
        // combiné dans l'app aujourd'hui.
        $sheets = [];
        if ($isPrimaire) {
            $rank = $class ? $this->calculateRank($user, $class, 1, $academicYear) : ['rank' => null, 'total' => 0];
            $qrCodeUri = null;
            if ($class) {
                // Pas de "semester" dans l'URL : BulletinVerifyController
                // l'interprète comme un bulletin annuel primaire.
                $verifyUrl = URL::signedRoute('bulletin.verify', [
                    'student_id'       => $user->id,
                    'class_id'         => $class->id,
                    'academic_year_id' => $academicYear->id,
                ]);
                $qrCodeUri = 'data:image/svg+xml;base64,'.base64_encode(
                    QrCode::format('svg')->size(120)->errorCorrection('H')->generate($verifyUrl)
                );
            }

            $sheets[] = $this->sheetFormatter->format(
                school: $user->school,
                academicYearName: $academicYear->name,
                periodLabel: 'Année scolaire complète',
                niveau: $studentInfo['level'],
                classe: $studentInfo['class'],
                student: $studentForSheet,
                effectif: $rank['total'],
                rank: $rank,
                bulletinData: $semestre1Data['data'],
                generalAverage: $moyenneAnnuelle,
                maxGrade: $overallMaxGrade,
                qrCodeUri: $qrCodeUri,
            );
        } else {
            foreach ([1 => $semestre1Data, 2 => $semestre2Data] as $semesterNumber => $semesterData) {
                $rank = $class ? $this->calculateRank($user, $class, $semesterNumber, $academicYear) : ['rank' => null, 'total' => 0];
                $qrCodeUri = null;
                if ($class) {
                    $verifyUrl = URL::signedRoute('bulletin.verify', [
                        'student_id'       => $user->id,
                        'class_id'         => $class->id,
                        'academic_year_id' => $academicYear->id,
                        'semester'         => $semesterNumber,
                    ]);
                    $qrCodeUri = 'data:image/svg+xml;base64,'.base64_encode(
                        QrCode::format('svg')->size(120)->errorCorrection('H')->generate($verifyUrl)
                    );
                }

                $sheets[] = $this->sheetFormatter->format(
                    school: $user->school,
                    academicYearName: $academicYear->name,
                    periodLabel: 'Semestre '.$semesterNumber,
                    niveau: $studentInfo['level'],
                    classe: $studentInfo['class'],
                    student: $studentForSheet,
                    effectif: $rank['total'],
                    rank: $rank,
                    bulletinData: $semesterData['data'],
                    generalAverage: (float) $semesterData['moyenne'],
                    maxGrade: $overallMaxGrade,
                    qrCodeUri: $qrCodeUri,
                );
            }
        }

        return view('student.bulletin-annuel', compact(
            'sheets',
            'semestre1Data',
            'semestre2Data',
            'moyenneAnnuelle',
            'decision',
            'studentInfo',
            'academicYear',
            'isPrimaire',
            'absenceCount',
            'overallMaxGrade'
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
     * Nombre d'absences d'un élève sur une période donnée (bornes
     * incluses). Sans bornes (année scolaire sans dates renseignées),
     * compte toutes les absences enregistrées pour l'élève.
     *
     * @param  array{0: ?Carbon, 1: ?Carbon}  $range
     */
    private function countAbsences(User $user, array $range): int
    {
        [$start, $end] = $range;

        return Attendance::where('user_id', $user->id)
            ->where('status', 'absent')
            ->when($start && $end, fn ($q) => $q->whereBetween('date', [$start->toDateString(), $end->toDateString()]))
            ->count();
    }

    /**
     * Découpe la période de l'année scolaire en deux semestres, au même
     * pivot que BulletinComputation::getCurrentSemester() (fin janvier /
     * début février) — Attendance n'a pas de colonne semestre.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function semesterDateRange(AcademicYear $academicYear, int $semester): array
    {
        if (! $academicYear->start_date || ! $academicYear->end_date) {
            return [null, null];
        }

        $start = Carbon::parse($academicYear->start_date);
        $end = Carbon::parse($academicYear->end_date);

        $pivot = Carbon::create($start->year, 2, 1);
        if ($pivot->lt($start)) {
            $pivot->addYear();
        }

        return $semester === 1
            ? [$start, $pivot->copy()->subDay()]
            : [$pivot, $end];
    }

    /**
     * Obtenir la décision du conseil de classe (texte + mention délégués à
     * BulletinComputation, la couleur d'affichage reste propre à cette vue).
     */
    private function getDecision($moyenneAnnuelle, float $maxGrade = 20.0)
    {
        $ratio = $maxGrade > 0 ? $moyenneAnnuelle / $maxGrade : 0;
        $color = match (true) {
            $ratio >= 10 / 20 => 'success',
            $ratio >= 8 / 20 => 'warning',
            default => 'danger',
        };

        return [
            'text' => $this->bulletinComputation->getDecisionText($moyenneAnnuelle, $maxGrade),
            'color' => $color,
            'mention' => $ratio >= 12 / 20 ? $this->bulletinComputation->getMention($moyenneAnnuelle, $maxGrade) : null,
        ];
    }
}

<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        
        // Récupérer l'année académique en cours
        $academicYear = AcademicYear::where('is_current', true)->first();
        
        if (!$academicYear) {
            return view('student.bulletin-senegal', [
                'error' => 'Aucune année académique en cours'
            ]);
        }

        // Informations sur la classe et le niveau
        $class = $user->schoolClass;
        $level = $class ? $class->level : null;
        $serie = $level ? $level->serie : null;

        // Récupérer les notes du semestre
        $grades = Grade::where('user_id', $user->id)
            ->where('semester', $semester)
            ->where('academic_year_id', $academicYear->id)
            ->with('subject')
            ->get();

        // Grouper les notes par matière et calculer les moyennes
        $bulletinData = $this->calculateBulletinData($grades, $level);

        // Calculer la moyenne générale pondérée
        $generalAverage = $this->calculateWeightedAverage($bulletinData);

        // Calculer le rang de l'élève
        $rankData = $this->calculateRank($user, $class, $semester, $academicYear);

        // Informations élève pour le bulletin
        $studentInfo = [
            'name' => $user->name,
            'class' => $class ? $class->name : 'Non assigné',
            'serie' => $serie,
            'identifier' => $user->identifier ?? '-',
            'academic_year' => $academicYear->name,
            'semester' => $semester,
            'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : '-',
            'level' => $level ? $level->name : '-',
        ];

        // Statistiques de classe
        $classStats = $this->calculateClassStats($class, $semester, $academicYear);

        return view('student.bulletin-senegal', compact(
            'bulletinData',
            'generalAverage',
            'studentInfo',
            'rankData',
            'classStats',
            'semester',
            'academicYear'
        ));
    }

    /**
     * Calcul des données du bulletin par matière
     */
    private function calculateBulletinData($grades, $level)
    {
        $bulletinData = [];
        
        // Grouper les notes par matière
        $gradesBySubject = $grades->groupBy('subject_id');
        
        foreach ($gradesBySubject as $subjectId => $subjectGrades) {
            $subject = Subject::find($subjectId);
            if (!$subject) continue;
            
            // Récupérer le coefficient depuis la relation niveau-matière
            $coefficient = 1;
            if ($level) {
                $levelSubject = DB::table('level_subject')
                    ->where('level_id', $level->id)
                    ->where('subject_id', $subjectId)
                    ->first();
                if ($levelSubject) {
                    $coefficient = $levelSubject->coefficient;
                }
            }
            
            // Extraire les notes par type
            $devoir1 = $subjectGrades->where('type', 'devoir1')->first();
            $devoir2 = $subjectGrades->where('type', 'devoir2')->first();
            $composition = $subjectGrades->where('type', 'composition')->first();
            
            // Calculer la moyenne (système sénégalais)
            // Moyenne des devoirs: (D1 + D2) / 2
            // Moyenne matière: Moyenne devoirs * 0.4 + Composition * 0.6
            $moyenneDevoirs = null;
            $moyenneMatiere = null;
            
            if ($devoir1 && $devoir2) {
                $moyenneDevoirs = ($devoir1->grade + $devoir2->grade) / 2;
            }
            
            if ($moyenneDevoirs !== null && $composition) {
                $moyenneMatiere = ($moyenneDevoirs * 0.4) + ($composition->grade * 0.6);
            } elseif ($composition) {
                $moyenneMatiere = $composition->grade;
            } elseif ($moyenneDevoirs !== null) {
                $moyenneMatiere = $moyenneDevoirs;
            }
            
            $bulletinData[] = [
                'subject' => $subject->name,
                'subject_code' => $subject->code,
                'coefficient' => $coefficient,
                'devoir1' => $devoir1 ? round($devoir1->grade, 2) : null,
                'devoir2' => $devoir2 ? round($devoir2->grade, 2) : null,
                'composition' => $composition ? round($composition->grade, 2) : null,
                'moyenne_devoirs' => $moyenneDevoirs ? round($moyenneDevoirs, 2) : null,
                'moyenne_matiere' => $moyenneMatiere ? round($moyenneMatiere, 2) : null,
                'points' => $moyenneMatiere ? round($moyenneMatiere * $coefficient, 2) : null,
                'appreciation' => $this->getAppreciation($moyenneMatiere),
            ];
        }
        
        // Trier par coefficient décroissant
        usort($bulletinData, function($a, $b) {
            return $b['coefficient'] - $a['coefficient'];
        });
        
        return $bulletinData;
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
     * Calculer le rang de l'élève dans sa classe
     */
    private function calculateRank($user, $class, $semester, $academicYear)
    {
        if (!$class) {
            return ['rank' => null, 'total' => 0];
        }
        
        // Récupérer tous les élèves de la classe
        $students = User::where('class_id', $class->id)
            ->where('role', 'eleve')
            ->where('status', 'approved')
            ->get();
        
        $averages = [];
        
        foreach ($students as $student) {
            $grades = Grade::where('user_id', $student->id)
                ->where('semester', $semester)
                ->where('academic_year_id', $academicYear->id)
                ->with('subject')
                ->get();
            
            $bulletinData = $this->calculateBulletinData($grades, $class->level);
            $average = $this->calculateWeightedAverage($bulletinData);
            
            $averages[] = [
                'student_id' => $student->id,
                'average' => $average
            ];
        }
        
        // Trier par moyenne décroissante
        usort($averages, function($a, $b) {
            return $b['average'] <=> $a['average'];
        });
        
        // Trouver le rang de l'élève
        $rank = null;
        foreach ($averages as $index => $avg) {
            if ($avg['student_id'] === $user->id) {
                $rank = $index + 1;
                break;
            }
        }
        
        return [
            'rank' => $rank,
            'total' => count($averages)
        ];
    }

    /**
     * Calculer les statistiques de la classe
     */
    private function calculateClassStats($class, $semester, $academicYear)
    {
        if (!$class) {
            return ['average' => null, 'highest' => null, 'lowest' => null];
        }
        
        $students = User::where('class_id', $class->id)
            ->where('role', 'eleve')
            ->where('status', 'approved')
            ->get();
        
        $averages = [];
        
        foreach ($students as $student) {
            $grades = Grade::where('user_id', $student->id)
                ->where('semester', $semester)
                ->where('academic_year_id', $academicYear->id)
                ->with('subject')
                ->get();
            
            $bulletinData = $this->calculateBulletinData($grades, $class->level);
            $average = $this->calculateWeightedAverage($bulletinData);
            
            if ($average > 0) {
                $averages[] = $average;
            }
        }
        
        if (empty($averages)) {
            return ['average' => null, 'highest' => null, 'lowest' => null];
        }
        
        return [
            'average' => round(array_sum($averages) / count($averages), 2),
            'highest' => round(max($averages), 2),
            'lowest' => round(min($averages), 2),
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
        if ($average >= 8) return 'Insuffisant';
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

        $bulletinData = $this->calculateBulletinData($grades, $level);
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

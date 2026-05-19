<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentGradesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Récupérer les enseignants de la classe de l'élève pour chaque matière
        $teachersBySubject = $this->getTeachersBySubject($user);

        // Récupérer les vraies notes groupées par matière
        $realGrades = $user->grades()
            ->with('subject')
            ->get();

        // Afficher uniquement les vraies notes (pas de données simulées)
        if ($realGrades->isEmpty()) {
            $grades = collect();
        } else {
            $grades = $realGrades->groupBy('subject.name')
                ->map(function ($subjectGrades) use ($teachersBySubject) {
                    $avg = $subjectGrades->avg('grade');
                    $subjectId = $subjectGrades->first()->subject->id;
                    $teacherName = $teachersBySubject[$subjectId] ?? 'Professeur';
                    
                    return [
                        'subject' => $subjectGrades->first()->subject->name,
                        'subject_color' => $subjectGrades->first()->subject->color ?? '#f59e0b',
                        'grades' => $subjectGrades->sortByDesc('date'),
                        'average' => round($avg, 2),
                        'coefficient' => $subjectGrades->first()->subject->coefficient ?? 1,
                        'teacher' => $teacherName,
                        'appreciation' => $this->getAppreciation($avg)
                    ];
                });
        }

        // Calculer la moyenne générale
        $generalAverage = $grades->isNotEmpty() ? collect($grades)->avg('average') : 0;

        // Informations élève pour le bulletin
        $studentInfo = [
            'name' => $user->name,
            'class' => $user->schoolClass->name ?? 'Non assigné',
            'identifier' => $user->identifier ?? '-',
            'academic_year' => '2025-2026',
            'trimester' => $this->getCurrentTrimester(),
        ];

        // Données d'évolution des notes pour le graphique
        $gradesEvolution = $this->getGradesEvolution($user);

        return view('student.grades', compact('grades', 'generalAverage', 'studentInfo', 'gradesEvolution'));
    }
    
    /**
     * Récupérer les enseignants par matière pour la classe de l'élève
     */
    private function getTeachersBySubject($user)
    {
        $teachersBySubject = [];
        
        if (!$user->class_id) {
            return $teachersBySubject;
        }
        
        // Récupérer les enseignants affectés à la classe de l'élève
        $classTeachers = DB::table('class_teacher')
            ->join('users', 'class_teacher.teacher_id', '=', 'users.id')
            ->where('class_teacher.class_id', $user->class_id)
            ->select('users.id', 'users.name')
            ->get();
        
        // Pour chaque enseignant, trouver les matières qu'il enseigne
        foreach ($classTeachers as $teacher) {
            $subjectIds = DB::table('teacher_subjects')
                ->where('teacher_id', $teacher->id)
                ->pluck('subject_id');
            
            foreach ($subjectIds as $subjectId) {
                if (!isset($teachersBySubject[$subjectId])) {
                    $teachersBySubject[$subjectId] = $teacher->name;
                }
            }
        }
        
        return $teachersBySubject;
    }

    /**
     * Afficher le bulletin scolaire
     */
    public function bulletin(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $trimester = $request->query('trimester', $this->getCurrentTrimester());

        // Récupérer les enseignants par matière
        $teachersBySubject = $this->getTeachersBySubject($user);

        // Récupérer les vraies notes uniquement
        $realGrades = $user->grades()
            ->with('subject')
            ->get();

        if ($realGrades->isEmpty()) {
            $grades = collect();
        } else {
            $grades = $realGrades->groupBy('subject.name')
                ->map(function ($subjectGrades) use ($teachersBySubject) {
                    $avg = $subjectGrades->avg('grade');
                    $subjectId = $subjectGrades->first()->subject->id;
                    $teacherName = $teachersBySubject[$subjectId] ?? 'Professeur';
                    
                    return [
                        'subject' => $subjectGrades->first()->subject->name,
                        'subject_color' => $subjectGrades->first()->subject->color ?? '#f59e0b',
                        'grades' => $subjectGrades->sortByDesc('date'),
                        'average' => round($avg, 2),
                        'coefficient' => $subjectGrades->first()->subject->coefficient ?? 1,
                        'teacher' => $teacherName,
                        'appreciation' => $this->getAppreciation($avg)
                    ];
                });
        }

        // Calculer la moyenne générale pondérée
        $totalCoef = collect($grades)->sum('coefficient');
        $weightedSum = collect($grades)->sum(function($g) {
            return $g['average'] * $g['coefficient'];
        });
        $generalAverage = $totalCoef > 0 ? $weightedSum / $totalCoef : 0;

        // Rang (à implémenter avec les vraies données quand disponibles)
        $rank = null;
        $totalStudents = $user->schoolClass ? $user->schoolClass->students()->count() : 0;

        // Informations élève
        $studentInfo = [
            'name' => $user->name,
            'class' => $user->schoolClass->name ?? 'Non assigné',
            'identifier' => $user->identifier ?? '-',
            'academic_year' => '2025-2026',
            'trimester' => $trimester,
            'date_of_birth' => $user->date_of_birth ?? '-',
            'level' => $user->schoolClass->level->name ?? '-',
        ];

        // Statistiques de classe (à calculer avec les vraies données)
        $classStats = [
            'average' => null,
            'highest' => null,
            'lowest' => null,
        ];

        return view('student.bulletin', compact(
            'grades', 
            'generalAverage', 
            'studentInfo', 
            'rank',
            'totalStudents',
            'classStats',
            'trimester'
        ));
    }

    /**
     * Obtenir le trimestre actuel
     */
    private function getCurrentTrimester()
    {
        $month = now()->month;
        if ($month >= 9 && $month <= 12) return 1;
        if ($month >= 1 && $month <= 3) return 2;
        return 3;
    }

    /**
     * Générer une appréciation basée sur la moyenne
     */
    private function getAppreciation($average)
    {
        if ($average >= 16) return 'Excellent travail. Continuez ainsi !';
        if ($average >= 14) return 'Très bon travail. Félicitations.';
        if ($average >= 12) return 'Bon travail. Peut encore progresser.';
        if ($average >= 10) return 'Travail satisfaisant. Des efforts à fournir.';
        if ($average >= 8) return 'Insuffisant. Doit travailler davantage.';
        return 'Travail très insuffisant. Ressaisissez-vous.';
    }

    /**
     * Évolution mensuelle des moyennes de l'élève sur toute l'année scolaire.
     */
    private function getGradesEvolution($user): array
    {
        $grades = $user->grades()
            ->with('subject')
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        if ($grades->isEmpty()) {
            return [];
        }

        $academicYear = $user->schoolClass?->academicYear;
        [$periodStart, $periodEnd] = $this->getAcademicYearMonthRange($academicYear);

        $evolution = [];
        $currentMonth = $periodStart->copy();

        while ($currentMonth <= $periodEnd) {
            $monthEnd = $currentMonth->copy()->endOfMonth();

            $gradesUpToMonth = $grades->filter(function ($grade) use ($monthEnd) {
                $date = $grade->date ?? $grade->created_at;

                return $date && $date->lte($monthEnd);
            });

            $monthAverage = null;
            if ($gradesUpToMonth->isNotEmpty()) {
                $weightedSum = 0;
                $totalCoef = 0;

                foreach ($gradesUpToMonth->groupBy('subject_id') as $subjectGrades) {
                    $subjectAvg = $subjectGrades->avg('grade');
                    if ($subjectAvg === null) {
                        continue;
                    }
                    $coefficient = $subjectGrades->first()->subject->coefficient ?? 1;
                    $weightedSum += $subjectAvg * $coefficient;
                    $totalCoef += $coefficient;
                }

                if ($totalCoef > 0) {
                    $monthAverage = round($weightedSum / $totalCoef, 2);
                }
            }

            $evolution[] = [
                'month' => $currentMonth->translatedFormat('M Y'),
                'grade' => $monthAverage,
                'count' => $gradesUpToMonth->count(),
            ];

            $currentMonth->addMonth();
        }

        $hasChartData = collect($evolution)->contains(fn ($row) => $row['grade'] !== null);

        if (! $hasChartData && $grades->isNotEmpty()) {
            $gradeStart = Carbon::parse($grades->min('date'))->startOfMonth();
            $gradeEnd = Carbon::parse($grades->max('date'))->endOfMonth();
            if ($gradeEnd->gt(now())) {
                $gradeEnd = now()->copy()->endOfMonth();
            }

            $evolution = [];
            $currentMonth = $gradeStart->copy();

            while ($currentMonth <= $gradeEnd) {
                $monthEnd = $currentMonth->copy()->endOfMonth();
                $gradesUpToMonth = $grades->filter(function ($grade) use ($monthEnd) {
                    $date = $grade->date ?? $grade->created_at;

                    return $date && $date->lte($monthEnd);
                });

                $monthAverage = null;
                if ($gradesUpToMonth->isNotEmpty()) {
                    $weightedSum = 0;
                    $totalCoef = 0;
                    foreach ($gradesUpToMonth->groupBy('subject_id') as $subjectGrades) {
                        $subjectAvg = $subjectGrades->avg('grade');
                        if ($subjectAvg === null) {
                            continue;
                        }
                        $coefficient = $subjectGrades->first()->subject->coefficient ?? 1;
                        $weightedSum += $subjectAvg * $coefficient;
                        $totalCoef += $coefficient;
                    }
                    if ($totalCoef > 0) {
                        $monthAverage = round($weightedSum / $totalCoef, 2);
                    }
                }

                $evolution[] = [
                    'month' => $currentMonth->translatedFormat('M Y'),
                    'grade' => $monthAverage,
                    'count' => $gradesUpToMonth->count(),
                ];
                $currentMonth->addMonth();
            }
        }

        return $evolution;
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function getAcademicYearMonthRange($academicYear): array
    {
        $now = Carbon::now();

        if ($academicYear?->start_date && $academicYear?->end_date) {
            $start = Carbon::parse($academicYear->start_date)->startOfMonth();
            $end = Carbon::parse($academicYear->end_date)->endOfMonth();

            if ($now->lt($start)) {
                $start->subYear();
                $end->subYear();
            }
        } else {
            $start = $now->copy()->month(9)->day(1)->startOfMonth();
            if ($now->month < 9) {
                $start->subYear();
            }
            $end = $start->copy()->addMonths(9)->endOfMonth();
        }

        if ($end->gt($now)) {
            $end = $now->copy()->endOfMonth();
        }

        if ($start->gt($end)) {
            $start = $end->copy()->startOfMonth();
        }

        return [$start, $end];
    }
}

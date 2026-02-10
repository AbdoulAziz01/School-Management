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
                        'subject_color' => $subjectGrades->first()->subject->color ?? '#3b82f6',
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
                        'subject_color' => $subjectGrades->first()->subject->color ?? '#3b82f6',
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
     * Obtenir l'évolution des notes de l'étudiant par mois
     */
    private function getGradesEvolution($user)
    {
        // Récupérer les notes triées par date
        $grades = $user->grades()
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        if ($grades->isEmpty()) {
            return [];
        }

        // Grouper par mois
        $monthNames = [
            1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Août',
            9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'
        ];

        $grouped = $grades->groupBy(function($grade) {
            $date = $grade->date ?? $grade->created_at;
            return $date ? $date->format('Y-m') : null;
        })->filter();

        $evolution = [];
        foreach ($grouped as $yearMonth => $monthGrades) {
            $month = (int) substr($yearMonth, 5, 2);
            $evolution[] = [
                'month' => $monthNames[$month] ?? $yearMonth,
                'grade' => round($monthGrades->avg('grade'), 1)
            ];
        }

        return $evolution;
    }
}

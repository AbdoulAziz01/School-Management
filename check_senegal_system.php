<?php

// Script de vérification du système scolaire sénégalais

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Level;
use App\Models\Subject;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Grade;
use App\Models\AcademicYear;

echo "=== VÉRIFICATION DU SYSTÈME SCOLAIRE SÉNÉGALAIS ===\n\n";

// 1. Année académique
$academicYear = AcademicYear::where('is_current', true)->first();
echo "📅 Année académique: {$academicYear->name}\n";
echo "   Début: {$academicYear->start_date} - Fin: {$academicYear->end_date}\n\n";

// 2. Niveaux et séries
echo "📚 NIVEAUX ET SÉRIES:\n";
echo str_repeat("-", 50) . "\n";

$levels = Level::with('subjects')->get();
foreach ($levels as $level) {
    echo "\n🎓 {$level->name} (Série {$level->serie}):\n";
    foreach ($level->subjects as $subject) {
        echo "   - {$subject->name} (coefficient: {$subject->pivot->coefficient})\n";
    }
}

// 3. Classes
echo "\n\n📖 CLASSES:\n";
echo str_repeat("-", 50) . "\n";

$classes = SchoolClass::with(['level', 'students'])->get();
foreach ($classes as $class) {
    $studentCount = $class->students->count();
    echo "   {$class->name}: {$studentCount} élèves\n";
}

// 4. Statistiques
echo "\n\n📊 STATISTIQUES:\n";
echo str_repeat("-", 50) . "\n";

echo "   Total utilisateurs: " . User::count() . "\n";
echo "   Élèves: " . User::where('role', 'eleve')->count() . "\n";
echo "   Professeurs: " . User::where('role', 'teacher')->count() . "\n";
echo "   Administrateurs: " . User::where('role', 'admin')->count() . "\n";
echo "   Classes: " . SchoolClass::count() . "\n";
echo "   Matières: " . Subject::count() . "\n";
echo "   Notes: " . Grade::count() . "\n";

// 5. Vérification notes par semestre
echo "\n\n📝 NOTES PAR SEMESTRE:\n";
echo str_repeat("-", 50) . "\n";

$gradeSem1 = Grade::where('semester', 1)->count();
$gradeSem2 = Grade::where('semester', 2)->count();
echo "   Semestre 1: {$gradeSem1} notes\n";
echo "   Semestre 2: {$gradeSem2} notes\n";

// 6. Types d'évaluations
echo "\n\n📋 TYPES D'ÉVALUATIONS:\n";
echo str_repeat("-", 50) . "\n";

$evaluationTypes = Grade::select('type')->distinct()->pluck('type');
foreach ($evaluationTypes as $type) {
    $count = Grade::where('type', $type)->count();
    echo "   {$type}: {$count} notes\n";
}

// 7. Exemple de bulletin d'un élève
echo "\n\n📄 EXEMPLE DE BULLETIN (Premier élève):\n";
echo str_repeat("=", 60) . "\n";

$student = User::where('role', 'eleve')->with('class.level')->first();
if ($student) {
    echo "Élève: {$student->name}\n";
    echo "Classe: {$student->class->name}\n";
    echo "Série: {$student->class->level->serie}\n\n";
    
    echo "SEMESTRE 1:\n";
    echo str_repeat("-", 60) . "\n";
    printf("%-35s %8s %8s %12s %8s\n", "Matière", "Coef", "Devoir1", "Devoir2", "Compo");
    echo str_repeat("-", 60) . "\n";
    
    $grades = Grade::where('user_id', $student->id)
        ->where('semester', 1)
        ->with('subject')
        ->get()
        ->groupBy('subject_id');
    
    $totalPoints = 0;
    $totalCoef = 0;
    
    foreach ($grades as $subjectId => $subjectGrades) {
        $subject = Subject::find($subjectId);
        $devoir1 = $subjectGrades->where('type', 'devoir1')->first();
        $devoir2 = $subjectGrades->where('type', 'devoir2')->first();
        $compo = $subjectGrades->where('type', 'composition')->first();
        
        $d1 = $devoir1 ? number_format($devoir1->grade, 2) : '-';
        $d2 = $devoir2 ? number_format($devoir2->grade, 2) : '-';
        $c = $compo ? number_format($compo->grade, 2) : '-';
        
        // Calcul moyenne matière: (Devoir1 + Devoir2) / 2 * 0.4 + Composition * 0.6
        if ($devoir1 && $devoir2 && $compo) {
            $moyDevoir = ($devoir1->grade + $devoir2->grade) / 2;
            $moyenne = ($moyDevoir * 0.4) + ($compo->grade * 0.6);
            $coef = $devoir1->coefficient;
            $totalPoints += $moyenne * $coef;
            $totalCoef += $coef;
        }
        
        printf("%-35s %8d %8s %8s %12s\n", 
            substr($subject->name, 0, 35), 
            $devoir1 ? $devoir1->coefficient : 1, 
            $d1, $d2, $c);
    }
    
    echo str_repeat("-", 60) . "\n";
    if ($totalCoef > 0) {
        $moyenneGenerale = $totalPoints / $totalCoef;
        echo "MOYENNE GÉNÉRALE SEMESTRE 1: " . number_format($moyenneGenerale, 2) . "/20\n";
    }
}

echo "\n\n✅ Vérification terminée!\n";

<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\Reports\BulletinReportService;
use Illuminate\Http\Request;

class BulletinVerifyController extends Controller
{
    public function show(Request $request, BulletinReportService $service)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Ce lien de vérification est invalide ou a expiré.');
        }

        $student  = User::findOrFail($request->integer('student_id'));
        $class    = SchoolClass::with(['level', 'academicYear', 'school'])->findOrFail($request->integer('class_id'));
        $year     = AcademicYear::findOrFail($request->integer('academic_year_id'));
        // Absent = bulletin annuel primaire (pas de semestre, voir
        // StudentBulletinController::annual()) ; présent = bulletin
        // semestriel secondaire, comportement inchangé.
        $semester = $request->filled('semester') ? $request->integer('semester') : null;

        abort_unless($student->isStudent(), 404);

        $bulletin = $semester !== null
            ? $service->buildSemesterBulletin($student, $class, $year, $semester)
            : $service->buildAnnualBulletinForStudent($student, $class, $year);

        return view('bulletin.verify', [
            'bulletin'  => $bulletin,
            'student'   => $student,
            'class'     => $class,
            'year'      => $year,
            'semester'  => $semester,
        ]);
    }
}

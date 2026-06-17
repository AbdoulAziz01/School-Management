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
        $semester = $request->integer('semester');

        abort_unless($student->isStudent(), 404);

        $bulletin = $service->buildSemesterBulletin($student, $class, $year, $semester);

        return view('bulletin.verify', [
            'bulletin'  => $bulletin,
            'student'   => $student,
            'class'     => $class,
            'year'      => $year,
            'semester'  => $semester,
        ]);
    }
}

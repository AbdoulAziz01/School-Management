<?php

namespace App\Http\View\Composers;

use App\Support\SchoolLogoStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SchoolBrandingComposer
{
    public function compose(View $view): void
    {
        $school = null;
        $schoolName = config('platform.name', 'EduManager');
        $schoolLogoDataUri = null;

        if (Auth::check()) {
            $schoolId = \App\Support\TenantSchool::id() ?? Auth::user()->school_id;
            if ($schoolId) {
                $school = \App\Models\School::find($schoolId) ?? Auth::user()->school;
            } elseif (Auth::user()->school_id) {
                $school = Auth::user()->school;
            }
            if ($school) {
                $schoolName = $school->name;
                $schoolLogoDataUri = SchoolLogoStorage::dataUri($school);
            }
        }

        $view->with('currentSchool', $school);
        $view->with('isFormationSchool', $school?->isFormation() ?? false);
        $view->with('usesFormationLmd', $school?->usesLmdGrading() ?? false);
        $view->with('schoolDisplayName', $schoolName);
        $view->with('schoolLogoDataUri', $schoolLogoDataUri);
    }
}

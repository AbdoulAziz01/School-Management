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
        $schoolName = config('app.name', 'EduManager');
        $schoolLogoDataUri = null;

        if (Auth::check() && Auth::user()->school_id) {
            $school = Auth::user()->school;
            if ($school) {
                $schoolName = $school->name;
                $schoolLogoDataUri = SchoolLogoStorage::dataUri($school);
            }
        }

        $view->with('currentSchool', $school);
        $view->with('schoolDisplayName', $schoolName);
        $view->with('schoolLogoDataUri', $schoolLogoDataUri);
    }
}

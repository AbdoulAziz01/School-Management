<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SchoolBrandingComposer
{
    public function compose(View $view): void
    {
        $school = null;
        $schoolName = config('app.name', 'EduManager');

        if (Auth::check() && Auth::user()->school_id) {
            $school = Auth::user()->school;
            if ($school) {
                $schoolName = $school->name;
            }
        }

        $view->with('currentSchool', $school);
        $view->with('schoolDisplayName', $schoolName);
    }
}

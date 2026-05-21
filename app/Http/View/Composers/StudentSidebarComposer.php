<?php

namespace App\Http\View\Composers;

use App\Support\DashboardAcademicYearContext;
use App\Support\StudentClassContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentSidebarComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        if (! $user || ! $user->isStudent()) {
            $view->with([
                'studentSidebarClass' => null,
                'studentSidebarClassLabel' => null,
                'studentSidebarYear' => null,
            ]);

            return;
        }

        $selectedYear = DashboardAcademicYearContext::resolve(request(), 'student');
        $studentClass = StudentClassContext::resolveForYear($user, $selectedYear);

        $view->with([
            'studentSidebarClass' => $studentClass,
            'studentSidebarClassLabel' => $studentClass?->display_name ?? 'Non assigné',
            'studentSidebarYear' => $selectedYear,
        ]);
    }
}

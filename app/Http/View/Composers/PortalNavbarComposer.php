<?php

namespace App\Http\View\Composers;

use App\Support\DashboardAcademicYearContext;
use App\Support\StudentClassContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalNavbarComposer
{
    public function compose(View $view): void
    {
        $scope = $view->getData()['portalScope'] ?? 'admin';
        $years = collect();
        $selectedYear = null;
        $roleSubLabel = $view->getData()['portalRoleSubLabel'] ?? null;

        if (Auth::check()) {
            $years = DashboardAcademicYearContext::allYears();
            $selectedYear = DashboardAcademicYearContext::resolve(request(), $scope);

            if ($scope === 'student' && $roleSubLabel === null) {
                $class = StudentClassContext::resolveForYear(Auth::user(), $selectedYear);
                $label = $class?->display_name ?? 'Non assigné';
                $roleSubLabel = $label !== 'Non assigné' ? $label : null;
            }
        }

        $view->with([
            'navbarAcademicYears' => $years,
            'navbarSelectedYear' => $selectedYear,
            'portalRoleSubLabel' => $roleSubLabel,
        ]);
    }
}

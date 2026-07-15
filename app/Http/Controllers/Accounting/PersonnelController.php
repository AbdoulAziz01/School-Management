<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Vue d'ensemble en lecture seule du personnel et des élèves pour le
 * directeur — comptage + listes par rôle, pour la transparence sur qui
 * travaille dans l'établissement. La création/modification des comptes
 * reste dans le panneau Admin (Admin\TeacherController,
 * Admin\StudentController, Admin\AccountingStaffController) : ce
 * contrôleur ne fait aucune écriture.
 */
class PersonnelController extends Controller
{
    /** @var array<string, array{label: string, roles: list<string>, icon: string}> */
    private const GROUPS = [
        'teachers' => ['label' => 'Enseignants', 'roles' => ['teacher', 'professeur'], 'icon' => 'fa-chalkboard-teacher'],
        'surveillants' => ['label' => 'Surveillants', 'roles' => ['surveillant'], 'icon' => 'fa-user-shield'],
        'admins' => ['label' => 'Administrateurs', 'roles' => ['admin'], 'icon' => 'fa-user-tie'],
        'comptables' => ['label' => 'Comptables', 'roles' => ['comptable'], 'icon' => 'fa-calculator'],
        'caissiers' => ['label' => 'Caissiers', 'roles' => ['caissier'], 'icon' => 'fa-cash-register'],
        'students' => ['label' => 'Élèves', 'roles' => ['eleve', 'student'], 'icon' => 'fa-user-graduate'],
    ];

    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $counts = [];
        foreach (self::GROUPS as $key => $group) {
            $counts[$key] = [
                ...$group,
                'count' => User::where('school_id', $schoolId)
                    ->whereIn('role', $group['roles'])
                    ->count(),
            ];
        }

        return view('accounting.directeur.personnel.index', ['counts' => $counts]);
    }

    public function show(Request $request, string $group)
    {
        abort_unless(isset(self::GROUPS[$group]), 404);

        $meta = self::GROUPS[$group];

        $query = User::where('school_id', $request->user()->school_id)
            ->whereIn('role', $meta['roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('identifier', 'like', "%{$search}%");
            });
        }

        $people = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('accounting.directeur.personnel.show', [
            'group' => $group,
            'meta' => $meta,
            'people' => $people,
        ]);
    }
}

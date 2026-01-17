<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherClassController extends Controller
{
    /**
     * Affiche le formulaire d'affectation des classes à un enseignant
     */
    public function edit(User $teacher)
    {
        if ($teacher->role !== User::ROLE_TEACHER) {
            abort(404, 'Cet utilisateur n\'est pas un enseignant');
        }

        $assignedClasses = $teacher->assignedClasses()->pluck('id')->toArray();
        $classes = SchoolClass::with('level', 'academicYear')
            ->orderBy('name')
            ->get();

        return view('admin.teachers.classes', compact('teacher', 'classes', 'assignedClasses'));
    }

    /**
     * Met à jour les affectations de classes d'un enseignant
     */
    public function update(Request $request, User $teacher)
    {
        if ($teacher->role !== User::ROLE_TEACHER) {
            abort(404, 'Cet utilisateur n\'est pas un enseignant');
        }

        $request->validate([
            'classes' => 'nullable|array',
            'classes.*' => 'exists:classes,id'
        ]);

        DB::transaction(function () use ($teacher, $request) {
            $teacher->assignedClasses()->sync($request->input('classes', []));
        });

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Les affectations de classes ont été mises à jour avec succès.');
    }
}

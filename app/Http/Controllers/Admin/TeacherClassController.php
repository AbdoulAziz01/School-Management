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
     * Affiche le formulaire d'affectation des classes à un enseignant.
     *
     * Le modèle "un seul enseignant titulaire par classe" (table
     * class_teacher) ne s'applique qu'au primaire, où un même enseignant
     * enseigne toutes les matières de sa classe. Au collège/lycée, une
     * classe a naturellement plusieurs enseignants (un par matière, voire
     * plusieurs sur une même matière) — cette page ne leur est donc pas
     * proposée : ces affectations passent par TeacherAssignmentController
     * (page "Affectations", par classe + matière, sans limite du nombre
     * d'enseignants).
     */
    public function edit(User $teacher)
    {
        if ($teacher->role !== User::ROLE_TEACHER) {
            abort(404, 'Cet utilisateur n\'est pas un enseignant');
        }

        $assignedClasses = $teacher->assignedClasses()->pluck('classes.id')->toArray();
        $classes = SchoolClass::with('level', 'academicYear')
            ->whereHas('academicYear', function($query) {
                $query->where('is_current', true);
            })
            ->whereHas('level', function ($query) {
                $query->where('cycle', 'primaire');
            })
            ->orderBy('name')
            ->get();

        // Une classe = un seul titulaire en temps normal (primaire
        // uniquement) : on prévient l'admin si une classe est déjà tenue
        // par un autre enseignant, pour qu'il sache qu'il devra forcer le
        // remplacement s'il continue.
        $otherTeacherByClass = DB::table('class_teacher')
            ->join('users', 'users.id', '=', 'class_teacher.teacher_id')
            ->whereIn('class_teacher.class_id', $classes->pluck('id'))
            ->where('class_teacher.teacher_id', '!=', $teacher->id)
            ->pluck('users.name', 'class_teacher.class_id');

        return view('admin.teachers.classes', compact('teacher', 'classes', 'assignedClasses', 'otherTeacherByClass'));
    }

    /**
     * Met à jour les affectations de classes d'un enseignant.
     *
     * Règle métier : une classe de PRIMAIRE n'a normalement qu'un seul
     * enseignant titulaire (voir edit()). Si une classe sélectionnée est
     * déjà affectée à un autre enseignant, on bloque et on demande
     * confirmation explicite (case "forcer") avant de la lui retirer —
     * pour les cas exceptionnels (remplacement suite à décès, maladie,
     * etc.). Toute classe de collège/lycée soumise est ignorée : ce
     * formulaire ne les propose plus, donc leur présence ne peut venir
     * que d'une requête modifiée à la main.
     */
    public function update(Request $request, User $teacher)
    {
        if ($teacher->role !== User::ROLE_TEACHER) {
            abort(404, 'Cet utilisateur n\'est pas un enseignant');
        }

        $request->validate([
            'classes' => 'nullable|array',
            'classes.*' => 'exists:classes,id',
            'force' => 'sometimes|boolean',
        ]);

        $primaireClassIds = SchoolClass::whereHas('level', fn ($q) => $q->where('cycle', 'primaire'))
            ->pluck('id')
            ->all();

        $selectedClassIds = array_values(array_intersect($request->input('classes', []), $primaireClassIds));
        $force = $request->boolean('force');

        $conflicts = DB::table('class_teacher')
            ->join('users', 'users.id', '=', 'class_teacher.teacher_id')
            ->join('classes', 'classes.id', '=', 'class_teacher.class_id')
            ->whereIn('class_teacher.class_id', $selectedClassIds)
            ->where('class_teacher.teacher_id', '!=', $teacher->id)
            ->select('classes.id as class_id', 'classes.name as class_name', 'users.name as teacher_name')
            ->get();

        if ($conflicts->isNotEmpty() && ! $force) {
            $details = $conflicts->map(fn ($c) => "{$c->class_name} (déjà à {$c->teacher_name})")->implode(', ');

            return back()
                ->withInput()
                ->with('error', "Classe(s) déjà affectée(s) à un autre enseignant : {$details}. Cochez « Forcer le remplacement » si c'est intentionnel (départ, maladie...).")
                ->with('pending_class_ids', $selectedClassIds);
        }

        DB::transaction(function () use ($teacher, $selectedClassIds, $conflicts) {
            if ($conflicts->isNotEmpty()) {
                DB::table('class_teacher')
                    ->whereIn('class_id', $conflicts->pluck('class_id'))
                    ->where('teacher_id', '!=', $teacher->id)
                    ->delete();
            }

            $teacher->assignedClasses()->sync($selectedClassIds);
        });

        return redirect()
            ->route('admin.teachers.show', $teacher)
            ->with('success', 'Les affectations de classes ont été mises à jour avec succès.');
    }
}

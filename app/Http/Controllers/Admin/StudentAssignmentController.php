<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class StudentAssignmentController extends Controller
{
    /**
     * Affiche la page d'affectation des élèves aux classes
     */
    public function index()
    {
        // Récupérer les élèves non affectés (sans classe)
        $unassignedStudents = User::where('role', 'student')
            ->where('status', 'approved')
            ->whereNull('class_id')
            ->get();

        // Récupérer les élèves déjà affectés avec pagination
        $assignedStudents = User::where('role', 'student')
            ->where('status', 'approved')
            ->whereNotNull('class_id')
            ->with('class')
            ->paginate(20);

        // Récupérer toutes les classes disponibles
        $classes = SchoolClass::with('level')->get();

        return view('admin.students.assign', compact('unassignedStudents', 'assignedStudents', 'classes'));
    }

    /**
     * Affecter un élève à une classe
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id'
        ]);

        $student = User::findOrFail($request->student_id);
        $student->class_id = $request->class_id;
        $student->save();

        return redirect()->back()->with('success', 'Élève affecté avec succès à la classe!');
    }

    /**
     * Retirer un élève d'une classe
     */
    public function unassign(User $student)
    {
        $student->class_id = null;
        $student->save();

        return redirect()->back()->with('success', 'Élève retiré de sa classe avec succès!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }
}

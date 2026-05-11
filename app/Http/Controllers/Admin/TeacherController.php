<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeacherController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Affiche la liste des enseignants
     */
    public function index(Request $request)
    {
        $query = User::whereIn('role', User::ROLE_TEACHER_ALIASES);

        // Recherche par nom, email ou identifiant
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('identifier', 'like', "%{$search}%");
            });
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $teachers = $query->orderBy('name')->paginate(15);

        return view('admin.teachers.index', compact('teachers'));
    }

    /**
     * Affiche le formulaire de création d'un enseignant
     */
    public function create()
    {
        return view('admin.teachers.create');
    }

    /**
     * Enregistre un nouvel enseignant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'identifier' => ['required', 'string', 'max:50', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
        ]);

        try {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'identifier' => $validated['identifier'],
                'password' => Hash::make($validated['password']),
                'role' => User::ROLE_TEACHER,
                'status' => User::STATUS_APPROVED,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'email_verified_at' => now(),
            ]);

            return redirect()
                ->route('admin.teachers.index')
                ->with('success', 'L\'enseignant a été créé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur création enseignant', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création de l\'enseignant.');
        }
    }

    /**
     * Affiche les détails d'un enseignant
     */
    public function show($id)
    {
        $teacher = User::whereIn('role', User::ROLE_TEACHER_ALIASES)
            ->with(['assignedClasses.level', 'assignedClasses.academicYear', 'assignedClasses.students', 'subjects'])
            ->findOrFail($id);
        return view('admin.teachers.show', compact('teacher'));
    }

    /**
     * Affiche le formulaire de modification d'un enseignant
     */
    public function edit(User $teacher)
    {
        // Garde-fou : la cible doit bien être un enseignant
        abort_unless($teacher->isTeacher(), 404, 'Enseignant introuvable.');

        return view('admin.teachers.edit', compact('teacher'));
    }

    /**
     * Met à jour les informations d'un enseignant
     */
    public function update(Request $request, User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404, 'Enseignant introuvable.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($teacher->id),
            ],
            'identifier' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users')->ignore($teacher->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        try {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'identifier' => $validated['identifier'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'status' => $validated['status'],
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $teacher->update($updateData);

            return redirect()
                ->route('admin.teachers.index')
                ->with('success', 'Les informations de l\'enseignant ont été mises à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour enseignant', [
                'teacher_id' => $teacher->id,
                'error'      => $e->getMessage(),
            ]);
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour.');
        }
    }

    /**
     * Supprime un enseignant
     */
    public function destroy(User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404, 'Enseignant introuvable.');

        try {
            if ($teacher->teacherAssignments()->exists()) {
                return back()
                    ->with('error', 'Impossible de supprimer cet enseignant car il a des affectations en cours.');
            }

            $teacher->delete();

            return redirect()
                ->route('admin.teachers.index')
                ->with('success', 'L\'enseignant a été supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur suppression enseignant', [
                'teacher_id' => $teacher->id,
                'error'      => $e->getMessage(),
            ]);
            return back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }
}

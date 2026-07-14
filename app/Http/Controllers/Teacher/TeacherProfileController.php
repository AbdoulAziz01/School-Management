<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class TeacherProfileController extends Controller
{
    /**
     * Afficher le profil de l'enseignant
     */
    public function index()
    {
        $teacher = Auth::user();
        
        return view('teacher.profile.index', compact('teacher'));
    }

    /**
     * Formulaire d'édition du profil
     */
    public function edit()
    {
        $teacher = Auth::user();
        
        return view('teacher.profile.edit', compact('teacher'));
    }

    /**
     * Mettre à jour le profil
     */
    public function update(Request $request)
    {
        $teacher = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $teacher->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
        ]);
        
        $teacher->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
        ]);
        
        return redirect()->route('teacher.profile.index')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        
        $teacher = Auth::user();
        
        if (!Hash::check($request->current_password, $teacher->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }
        
        $teacher->forceFill([
            'password' => Hash::make($request->password),
        ])->save();
        
        return redirect()->route('teacher.profile.index')
            ->with('success', 'Mot de passe mis à jour avec succès.');
    }

    /**
     * Mettre à jour la photo de profil
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $teacher = Auth::user();
        
        // Supprimer l'ancienne photo si elle existe
        if ($teacher->profile_photo_path) {
            Storage::disk('public')->delete($teacher->profile_photo_path);
        }
        
        // Enregistrer la nouvelle photo
        $path = $request->file('photo')->store('profile-photos', 'public');
        
        $teacher->update([
            'profile_photo_path' => $path,
        ]);
        
        return redirect()->route('teacher.profile.index')
            ->with('success', 'Photo de profil mise à jour avec succès.');
    }
}

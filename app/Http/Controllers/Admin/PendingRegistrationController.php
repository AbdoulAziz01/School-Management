<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Support\TenantSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PendingRegistrationController extends Controller
{
/**
 * Affiche la liste des inscriptions en attente
 */
public function pending()
{
    $students = User::whereIn('role', ['eleve', 'student'])
        ->where('status', 'pending')
        ->with('class')
        ->orderBy('created_at', 'desc')
        ->get();

    Log::info('Inscriptions en attente', [
        'count' => $students->count(),
    ]);

    $classes = SchoolClass::with('academicYear')
        ->whereHas('academicYear', function ($query) {
            $query->where('is_current', true);
        })
        ->orderedByLevel()
        ->get();

    // Fallback : si aucune classe n'est liée à l'année courante, on prend toutes les classes de l'école
    if ($classes->isEmpty()) {
        $classes = SchoolClass::with('academicYear')
            ->orderedByLevel()
            ->get();
    }

    return view('admin.registrations.pending', [
        'pendingUsers' => $students,
        'classes' => $classes,
    ]);
}

/**
 * Affiche la liste des inscriptions en attente (version paginée)
 */
public function index()
{
    $pendingUsers = User::with(['subjects'])
        ->whereIn('role', ['eleve', 'student'])
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    return view('admin.registrations.pending', compact('pendingUsers'));
}

    public function approve(Request $request, User $user)
    {
        // Garde-fou : on ne valide que des élèves en attente
        abort_unless(
            in_array($user->role, ['eleve', 'student'], true) && $user->status === 'pending',
            404,
            'Inscription introuvable ou non éligible.'
        );

        $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;

        $classRule = $schoolId
            ? Rule::exists('classes', 'id')->where('school_id', $schoolId)
            : 'exists:classes,id';

        $request->validate(['class_id' => ['required', $classRule]]);

        try {
            DB::beginTransaction();
            
            $user->forceFill([
                'status' => 'approved',
                'class_id' => $request->class_id,
            ])->save();
            
            // Envoyer une notification à l'utilisateur
            // Mail::to($user->email)->send(new RegistrationApproved($user));
            
            DB::commit();
            
            return redirect()
                ->route('admin.registrations.pending')
                ->with('success', 'Inscription approuvée avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'approbation de l\'inscription: ' . $e->getMessage());
            
            return redirect()
                ->route('admin.registrations.pending')
                ->with('error', 'Une erreur est survenue lors de l\'approbation: ' . $e->getMessage());
        }
    }
    
    public function reject(User $user)
    {
        abort_unless(
            in_array($user->role, ['eleve', 'student'], true) && $user->status === 'pending',
            404,
            'Inscription introuvable ou non éligible.'
        );

        try {
            $user->status = 'rejected';
            $user->save();
            
            // Envoyer une notification à l'utilisateur
            // Mail::to($user->email)->send(new RegistrationRejected($user));
            
            return redirect()
                ->route('admin.registrations.pending')
                ->with('success', 'Inscription rejetée avec succès');
                
        } catch (\Exception $e) {
            Log::error('Erreur lors du rejet de l\'inscription: ' . $e->getMessage());
            
            return redirect()
                ->route('admin.registrations.pending')
                ->with('error', 'Une erreur est survenue lors du rejet');
        }
    }
    
    private function extractLevelFromClassName($className)
    {
        // Extraire le niveau à partir du nom de la classe (ex: '6ème A' -> 6)
        if (preg_match('/(\d+)/', $className, $matches)) {
            return (int)$matches[1];
        }
        
        return null;
    }
}

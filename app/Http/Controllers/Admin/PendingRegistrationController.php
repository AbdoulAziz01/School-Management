<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PendingRegistrationController extends Controller
{
/**
 * Affiche la liste des inscriptions en attente
 */
public function pending()
{
    $students = User::where('status', 'pending')
        ->with('class')
        ->orderBy('created_at', 'desc')
        ->get();
    
    // Journalisation pour le débogage
    \Log::info('Inscriptions en attente', [
        'count' => $students->count(),
        'students' => $students->pluck('name', 'id')
    ]);
    
    return view('admin.registrations.pending', ['pendingUsers' => $students]);
}

/**
 * Affiche la liste des inscriptions en attente (version paginée)
 */
public function index()
{
    $pendingUsers = User::with(['subjects'])
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    
    return view('admin.registrations.pending', compact('pendingUsers'));
}

    public function approve(Request $request, User $user)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
        ]);

        try {
            DB::beginTransaction();
            
            // Mettre à jour le statut et la classe de l'utilisateur
            $user->update([
                'status' => 'approved',
                'class_id' => $request->class_id
            ]);
            
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

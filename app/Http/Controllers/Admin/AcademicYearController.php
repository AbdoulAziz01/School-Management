<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Services\StudentClassPromotionService;
use App\Support\AcademicYearProvisioner;
use App\Support\DashboardAcademicYearContext;
use App\Support\SchoolSubjectProvisioner;
use App\Support\TenantSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $years = AcademicYear::orderBy('start_date', 'desc')->get();
        return view('admin.academic-years.index', compact('years'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.academic-years.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateAcademicYear($request);

        try {
            DB::beginTransaction();

            $previousYear = ($request->has('is_current') && $request->is_current)
                ? AcademicYear::where('is_current', true)->first()
                : null;

            // Si on définit cette année comme année courante, on désactive les autres
            if ($request->has('is_current') && $request->is_current) {
                AcademicYear::where('is_current', true)->update(['is_current' => false]);
            }

            if ($previousYear && $request->has('is_current') && $request->is_current) {
                $previousYear->update(['is_closed' => true]);
            }

            $year = AcademicYear::create($validated);

            if ($request->has('is_current') && $request->is_current) {
                DashboardAcademicYearContext::select($year);
            }

            SchoolSubjectProvisioner::ensureForSchool($year->school_id ?? TenantSchool::id());

            $provisionSummary = AcademicYearProvisioner::provision($year, $previousYear);
            $provisionMessage = $this->formatProvisionMessage($provisionSummary);

            $transitionMessage = $this->runYearTransition($previousYear, $year);
            
            DB::commit();
            
            return redirect()
                ->route('admin.academic-years.index')
                ->with('success', 'Année scolaire créée avec succès.'.$provisionMessage.$transitionMessage);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création de l\'année scolaire.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AcademicYear $academicYear)
    {
        $academicYear->load([
            'classes' => fn ($q) => $q->orderedByLevel(),
            'classes.level',
            'classes.students',
            'classes.teachers',
            'teacherAssignments.teacher', 
            'teacherAssignments.subject', 
            'teacherAssignments.schoolClass'
        ]);
        
        // Calculate real statistics
        $academicYear->classes_count = $academicYear->classes->count();
        $academicYear->teacher_assignments_count = $academicYear->teacherAssignments->count();
        $academicYear->students_count = $academicYear->classes->sum(function($class) {
            return $class->students->count();
        });
        
        // Compter les professeurs uniques via la table class_teacher
        // On utilise une requête directe pour être sûr de compter tous les professeurs
        $classIds = $academicYear->classes->pluck('id')->toArray();
        $uniqueTeachersCount = 0;
        
        if (count($classIds) > 0) {
            $uniqueTeachersCount = DB::table('class_teacher')
                ->join('users', 'class_teacher.teacher_id', '=', 'users.id')
                ->whereIn('class_teacher.class_id', $classIds)
                ->whereIn('users.role', ['teacher', 'enseignant'])
                ->distinct('class_teacher.teacher_id')
                ->count('class_teacher.teacher_id');
        }
        
        $academicYear->unique_teachers_count = $uniqueTeachersCount;
        
        // Add students_count to each class for display
        foreach ($academicYear->classes as $class) {
            $class->students_count = $class->students->count();
        }
        
        return view('admin.academic-years.show', compact('academicYear'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic-years.edit', compact('academicYear'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $this->validateAcademicYear($request);

        try {
            DB::beginTransaction();

            $previousYear = null;
            if ($request->has('is_current') && $request->is_current && ! $academicYear->is_current) {
                $previousYear = AcademicYear::where('is_current', true)
                    ->where('id', '!=', $academicYear->id)
                    ->first();
            }

            // Si on définit cette année comme année courante, on désactive les autres
            if ($request->has('is_current') && $request->is_current) {
                AcademicYear::where('is_current', true)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['is_current' => false]);

                if ($previousYear) {
                    $previousYear->update(['is_closed' => true]);
                }
            }

            $academicYear->update($validated);

            if ($request->has('is_current') && $request->is_current) {
                DashboardAcademicYearContext::select($academicYear->fresh());
                $provisionSummary = AcademicYearProvisioner::provision($academicYear->fresh(), $previousYear);
                $provisionMessage = $this->formatProvisionMessage($provisionSummary);
            } else {
                $provisionMessage = '';
            }

            $transitionMessage = ($request->has('is_current') && $request->is_current)
                ? $this->runYearTransition($previousYear, $academicYear->fresh())
                : '';
            
            DB::commit();
            
            return redirect()
                ->route('admin.academic-years.index')
                ->with('success', 'Année scolaire mise à jour avec succès.'.$provisionMessage.$transitionMessage);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour de l\'année scolaire.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        // Vérifier si l'année scolaire est utilisée par des classes
        if ($academicYear->classes()->exists()) {
            return back()
                ->with('error', 'Impossible de supprimer cette année scolaire car elle est utilisée par des classes.');
        }

        // Vérifier si l'année scolaire est utilisée par des affectations de professeurs
        if ($academicYear->teacherAssignments()->exists()) {
            return back()
                ->with('error', 'Impossible de supprimer cette année scolaire car elle est utilisée par des affectations de professeurs.');
        }

        try {
            $academicYear->delete();
            return redirect()
                ->route('admin.academic-years.index')
                ->with('success', 'Année scolaire supprimée avec succès.');
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Une erreur est survenue lors de la suppression de l\'année scolaire.');
        }
    }

    /**
     * Définir une année scolaire comme année en cours
     */
    public function setCurrent(AcademicYear $academicYear)
    {
        try {
            DB::beginTransaction();

            $previousYear = AcademicYear::where('is_current', true)
                ->where('id', '!=', $academicYear->id)
                ->first();

            // Désactiver toutes les autres années
            AcademicYear::where('is_current', true)
                ->where('id', '!=', $academicYear->id)
                ->update(['is_current' => false]);

            if ($previousYear) {
                $previousYear->update(['is_closed' => true]);
            }

            // Définir l'année sélectionnée comme année en cours
            $academicYear->update(['is_current' => true]);

            DashboardAcademicYearContext::select($academicYear->fresh());
            $provisionSummary = AcademicYearProvisioner::provision($academicYear->fresh(), $previousYear);
            $provisionMessage = $this->formatProvisionMessage($provisionSummary);

            $transitionMessage = $this->runYearTransition($previousYear, $academicYear->fresh());
            
            DB::commit();
            
            return back()
                ->with('success', 'L\'année scolaire a été définie comme année en cours.'.$provisionMessage.$transitionMessage);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Une erreur est survenue lors du changement d\'année en cours.');
        }
    }

    /**
     * Marquer une année scolaire comme terminée (bloque la saisie des notes).
     */
    public function close(AcademicYear $academicYear)
    {
        if ($academicYear->is_closed) {
            return back()->with('info', 'Cette année scolaire est déjà marquée comme terminée.');
        }

        $academicYear->update(['is_closed' => true]);

        $message = 'L\'année scolaire « '.$academicYear->name.' » est marquée comme terminée.';
        if ($academicYear->is_current) {
            $message .= ' La saisie des notes est désormais bloquée pour tous les enseignants.';
        } else {
            $message .= ' Les passages en classe supérieure sont maintenant visibles.';
        }

        return back()->with('success', $message);
    }

    /**
     * Rouvrir une année scolaire (annuler le statut terminée).
     */
    public function reopen(AcademicYear $academicYear)
    {
        $academicYear->update(['is_closed' => false]);

        return back()->with('success', 'L\'année scolaire « '.$academicYear->name.' » n\'est plus marquée comme terminée.');
    }

    /** @return array<string, mixed> */
    private function validateAcademicYear(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'sometimes|boolean',
        ]);

        if (empty($validated['end_date'])) {
            $validated['end_date'] = null;
        }

        return $validated;
    }

    private function runYearTransition(?AcademicYear $previousYear, AcademicYear $newYear): string
    {
        if (! $previousYear || $previousYear->id === $newYear->id) {
            return '';
        }

        $summary = app(StudentClassPromotionService::class)->processYearTransition($previousYear, $newYear);

        if ($summary['promoted'] === 0 && $summary['graduated'] === 0 && $summary['repeated'] === 0) {
            return '';
        }

        $parts = [];
        if ($summary['promoted'] > 0) {
            $parts[] = "{$summary['promoted']} passage(s) en classe supérieure";
        }
        if ($summary['graduated'] > 0) {
            $parts[] = "{$summary['graduated']} diplômé(s) (Terminale)";
        }
        if ($summary['repeated'] > 0) {
            $parts[] = "{$summary['repeated']} redoublant(s) maintenu(s) au même niveau";
        }

        return ' Passages automatiques : '.implode(', ', $parts).'.';
    }

    /**
     * Génère classes / affectations pour une année vide (action manuelle).
     */
    public function provision(AcademicYear $academicYear)
    {
        $sourceYear = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $academicYear->school_id)
            ->where('id', '!=', $academicYear->id)
            ->whereHas('classes')
            ->orderByDesc('start_date')
            ->first();

        $summary = AcademicYearProvisioner::provision($academicYear, $sourceYear);

        if ($summary['skipped'] ?? false) {
            return back()->with('warning', $summary['message']);
        }

        if (($summary['classes'] ?? 0) === 0) {
            return back()->with('error', $summary['message'] ?: 'Impossible de générer les classes.');
        }

        return back()->with('success', 'Structure générée : '.$summary['message']);
    }

    /** @param  array<string, mixed>  $summary */
    private function formatProvisionMessage(array $summary): string
    {
        if ($summary['skipped'] ?? false) {
            return '';
        }

        if (($summary['classes'] ?? 0) === 0) {
            return '';
        }

        return ' '.$summary['message'];
    }
}

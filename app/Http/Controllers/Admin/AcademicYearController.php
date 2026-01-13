<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'sometimes|boolean'
        ]);

        try {
            DB::beginTransaction();

            // Si on définit cette année comme année courante, on désactive les autres
            if ($request->has('is_current') && $request->is_current) {
                AcademicYear::where('is_current', true)->update(['is_current' => false]);
            }

            AcademicYear::create($validated);
            
            DB::commit();
            
            return redirect()
                ->route('academic-years.index')
                ->with('success', 'Année scolaire créée avec succès.');
                
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
        $academicYear->load(['classes', 'teacherAssignments.teacher', 'teacherAssignments.subject']);
        
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'sometimes|boolean'
        ]);

        try {
            DB::beginTransaction();

            // Si on définit cette année comme année courante, on désactive les autres
            if ($request->has('is_current') && $request->is_current) {
                AcademicYear::where('is_current', true)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['is_current' => false]);
            }

            $academicYear->update($validated);
            
            DB::commit();
            
            return redirect()
                ->route('academic-years.index')
                ->with('success', 'Année scolaire mise à jour avec succès.');
                
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
                ->route('academic-years.index')
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

            // Désactiver toutes les autres années
            AcademicYear::where('is_current', true)
                ->where('id', '!=', $academicYear->id)
                ->update(['is_current' => false]);

            // Définir l'année sélectionnée comme année en cours
            $academicYear->update(['is_current' => true]);
            
            DB::commit();
            
            return back()
                ->with('success', 'L\'année scolaire a été définie comme année en cours.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Une erreur est survenue lors du changement d\'année en cours.');
        }
    }
}

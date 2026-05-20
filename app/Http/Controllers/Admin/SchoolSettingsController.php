<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Support\SchoolLogoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolSettingsController extends Controller
{
    public function edit(): View
    {
        $school = $this->currentSchool();

        $staffMembers = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->whereIn('role', User::ROLE_SCHOOL_STAFF)
            ->orderBy('role')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'identifier', 'role', 'status', 'created_at']);

        return view('admin.school.settings', compact('school', 'staffMembers'));
    }

    public function update(Request $request): RedirectResponse
    {
        $school = $this->currentSchool();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'],
            'remove_logo' => ['sometimes', 'boolean'],
        ]);

        $school->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'slug' => School::slugFromName($validated['name'], $school->id),
        ]);

        if ($request->boolean('remove_logo')) {
            SchoolLogoStorage::clear($school);
        } elseif ($request->hasFile('logo')) {
            SchoolLogoStorage::store($school, $request->file('logo'));
        }

        return redirect()
            ->route('admin.school.settings.edit')
            ->with('success', 'Les informations de votre établissement ont été mises à jour.');
    }

    private function currentSchool(): School
    {
        $schoolId = auth()->user()->school_id;

        abort_unless($schoolId, 403, 'Aucun établissement associé à votre compte.');

        return School::findOrFail($schoolId);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Support\SchoolLogoStorage;
use App\Support\SchoolProfile;
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

        $validated = $request->validate(array_merge(
            SchoolProfile::operationalRules(),
            [
                'logo'        => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'],
                'remove_logo' => ['sometimes', 'boolean'],
            ]
        ));

        $school->update(SchoolProfile::operationalAttributes($validated));

        if ($request->boolean('remove_logo')) {
            SchoolLogoStorage::clear($school);
        } elseif ($request->hasFile('logo')) {
            SchoolLogoStorage::store($school, $request->file('logo'));
        }

        return redirect()
            ->route('admin.school.settings.edit')
            ->with('success', 'Les informations de contact et de communication ont été mises à jour.');
    }

    private function currentSchool(): School
    {
        $schoolId = auth()->user()->school_id;

        abort_unless($schoolId, 403, 'Aucun établissement associé à votre compte.');

        return School::findOrFail($schoolId);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;

class SchoolRegistrationController extends Controller
{
    public function subjects(string $code): JsonResponse
    {
        $school = School::where('code', strtoupper(trim($code)))
            ->where('is_active', true)
            ->first();

        if (! $school) {
            return response()->json(['message' => 'Code établissement invalide ou école inactive.'], 404);
        }

        $subjects = Subject::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
            ],
            'subjects' => $subjects,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Support\PlatformMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnassignedStudentController extends Controller
{
    public function index(Request $request): View
    {
        $query = PlatformMetrics::unassignedStudentsQuery();

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->integer('school_id'));
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->trim()->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('identifier', 'ilike', "%{$search}%")
                    ->orWhere('user_id', 'ilike', "%{$search}%");
            });
        }

        $students = $query
            ->orderBy('school_id')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $schools = School::query()->orderBy('name')->get(['id', 'name']);

        return view('platform.students.unassigned', compact('students', 'schools'));
    }
}

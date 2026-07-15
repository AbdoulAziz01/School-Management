<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\LedgerEntry;
use App\Models\School;
use Illuminate\Http\Request;

/**
 * Journal des opérations (grand livre) — même contrôleur pour le comptable
 * et le directeur (routes déclarées deux fois, une par portail), les deux
 * ayant la permission *.consulter sur ecriture/journal/grand_livre.
 */
class LedgerController extends Controller
{
    public function index(Request $request)
    {
        $school = School::find($request->user()->school_id);

        $query = LedgerEntry::where('school_id', $school->id)->orderByDesc('recorded_at');

        if ($request->filled('type')) {
            $query->where('entry_type', $request->type);
        }

        if ($request->filled('from')) {
            $query->whereDate('recorded_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('recorded_at', '<=', $request->date('to'));
        }

        $entries = $query->paginate(30)->withQueryString();

        return view('accounting.shared.ledger.index', ['entries' => $entries]);
    }
}

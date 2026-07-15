<?php

namespace App\Services;

use App\Models\EmployeeSalaryProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Historique des salaires : définir un nouveau salaire ferme la ligne
 * active (effective_to) et en ouvre une nouvelle plutôt que d'écraser le
 * montant — l'historique complet reste consultable (aucune donnée
 * financière n'est perdue).
 */
class EmployeeSalaryProfileService
{
    public function currentProfile(User $user): ?EmployeeSalaryProfile
    {
        return EmployeeSalaryProfile::where('user_id', $user->id)
            ->whereNull('effective_to')
            ->first();
    }

    public function setSalary(User $user, float $monthlyAmount, User $setBy, ?Carbon $effectiveFrom = null): EmployeeSalaryProfile
    {
        $effectiveFrom ??= now()->startOfDay();

        return DB::transaction(function () use ($user, $monthlyAmount, $setBy, $effectiveFrom) {
            $current = $this->currentProfile($user);

            if ($current && round((float) $current->monthly_amount, 2) === round($monthlyAmount, 2)) {
                return $current;
            }

            if ($current) {
                $current->update(['effective_to' => $effectiveFrom->copy()->subDay()]);
            }

            return EmployeeSalaryProfile::create([
                // school_id explicite (pas seulement via TenantSchool::id()
                // ambiant) : ce service doit rester correct depuis une
                // commande planifiée (génération mensuelle, Phase 6.4), qui
                // n'a pas de contexte HTTP/session.
                'school_id' => $user->school_id,
                'user_id' => $user->id,
                'monthly_amount' => $monthlyAmount,
                'effective_from' => $effectiveFrom,
                'created_by' => $setBy->id,
            ]);
        });
    }

    /** @return Collection<int, EmployeeSalaryProfile> */
    public function history(User $user): Collection
    {
        return EmployeeSalaryProfile::where('user_id', $user->id)
            ->orderByDesc('effective_from')
            ->get();
    }
}

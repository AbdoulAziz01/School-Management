<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\FeeAmount;
use App\Models\FeeType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Configuration des frais scolaires (types + montants par niveau/année) —
 * extrait pour que FeeTypeController reste un simple relais HTTP, sur le
 * modèle de ClassStatisticsService.
 */
class FeeConfigurationService
{
    /** @param array{code: string, name: string, category: string, is_recurring?: bool} $data */
    public function createFeeType(array $data): FeeType
    {
        return FeeType::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'category' => $data['category'],
            'is_recurring' => $data['is_recurring'] ?? false,
        ]);
    }

    /** @param array{code: string, name: string, category: string, is_recurring?: bool} $data */
    public function updateFeeType(FeeType $feeType, array $data): FeeType
    {
        $feeType->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'category' => $data['category'],
            'is_recurring' => $data['is_recurring'] ?? false,
        ]);

        return $feeType;
    }

    /**
     * Un montant par niveau (Level) pour un type de frais et une année
     * donnée. level_id null = s'applique à tous les niveaux qui n'ont pas
     * de montant spécifique défini.
     */
    public function setAmount(FeeType $feeType, AcademicYear $academicYear, ?int $levelId, float $amount): FeeAmount
    {
        return FeeAmount::updateOrCreate(
            [
                'fee_type_id' => $feeType->id,
                'academic_year_id' => $academicYear->id,
                'level_id' => $levelId,
            ],
            [
                // school_id repris directement du FeeType (pas de dépendance
                // au contexte HTTP ambiant TenantSchool::id()).
                'school_id' => $feeType->school_id,
                'amount' => $amount,
            ]
        );
    }

    /**
     * Enregistre plusieurs montants (un par niveau + "tous niveaux") pour un
     * même type de frais/année en une seule transaction — soit tous les
     * montants soumis sont enregistrés, soit aucun (formulaire à plusieurs
     * lignes, voir accounting.directeur.fee-types.amounts).
     *
     * @param  array<string, mixed>  $amounts  clé "all" ou level_id => montant
     */
    public function updateAmounts(FeeType $feeType, AcademicYear $academicYear, array $amounts): void
    {
        DB::transaction(function () use ($feeType, $academicYear, $amounts) {
            if (array_key_exists('all', $amounts) && $amounts['all'] !== null && $amounts['all'] !== '') {
                $this->setAmount($feeType, $academicYear, null, (float) $amounts['all']);
            }

            foreach ($amounts as $key => $value) {
                if ($key === 'all' || $value === null || $value === '') {
                    continue;
                }

                $this->setAmount($feeType, $academicYear, (int) $key, (float) $value);
            }
        });
    }

    /** @return Collection<int, FeeAmount> montants existants, indexés par level_id (0 = "tous niveaux") */
    public function amountsFor(FeeType $feeType, AcademicYear $academicYear): Collection
    {
        return $feeType->amounts()
            ->where('academic_year_id', $academicYear->id)
            ->get()
            ->keyBy(fn (FeeAmount $amount) => $amount->level_id ?? 0);
    }

    /**
     * Montant applicable pour un élève d'un niveau donné : montant
     * spécifique au niveau s'il existe, sinon le montant "tous niveaux".
     */
    public function amountForLevel(FeeType $feeType, AcademicYear $academicYear, ?int $levelId): ?float
    {
        $amounts = $this->amountsFor($feeType, $academicYear);

        return $amounts->get($levelId)?->amount ?? $amounts->get(0)?->amount;
    }
}

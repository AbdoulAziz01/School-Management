<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\FeeAmount;
use App\Models\FeeType;
use App\Models\Level;
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
     * Grille complète [level_id => [fee_type_id => montant]] pour l'année en
     * cours — une seule requête au lieu d'une par type de frais, pour
     * alimenter le tableau croisé Niveau × Type de frais. level_id = 0
     * représente la ligne "Tous niveaux" (valeur par défaut).
     *
     * @param  Collection<int, FeeType>  $feeTypes
     * @return array<int, array<int, float>>
     */
    public function amountsGrid(Collection $feeTypes, AcademicYear $academicYear): array
    {
        $grid = [];

        FeeAmount::whereIn('fee_type_id', $feeTypes->pluck('id'))
            ->where('academic_year_id', $academicYear->id)
            ->get(['level_id', 'fee_type_id', 'amount'])
            ->each(function (FeeAmount $row) use (&$grid) {
                $grid[$row->level_id ?? 0][$row->fee_type_id] = (float) $row->amount;
            });

        return $grid;
    }

    /**
     * Montant applicable pour une case de la grille : valeur spécifique au
     * niveau si elle existe, sinon repli sur "Tous niveaux".
     *
     * @param  array<int, array<int, float>>  $grid
     */
    public function amountFromGrid(array $grid, int $levelId, int $feeTypeId): ?float
    {
        return $grid[$levelId][$feeTypeId] ?? $grid[0][$feeTypeId] ?? null;
    }

    /**
     * Enregistre en une transaction tous les montants d'un niveau pour
     * l'année en cours — un par type de frais. Une valeur vide/nulle
     * supprime la ligne existante (le frais ne s'applique plus à ce niveau,
     * avec repli automatique sur "Tous niveaux" s'il existe).
     *
     * @param  array<int, mixed>  $amounts  fee_type_id => montant|null
     */
    public function updateLevelAmounts(Level $level, AcademicYear $academicYear, array $amounts): void
    {
        DB::transaction(function () use ($level, $academicYear, $amounts) {
            foreach ($amounts as $feeTypeId => $value) {
                if ($value === null || $value === '') {
                    FeeAmount::where([
                        'fee_type_id' => (int) $feeTypeId,
                        'academic_year_id' => $academicYear->id,
                        'level_id' => $level->id,
                    ])->delete();

                    continue;
                }

                $this->setAmount(
                    FeeType::findOrFail($feeTypeId),
                    $academicYear,
                    $level->id,
                    (float) $value
                );
            }
        });
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

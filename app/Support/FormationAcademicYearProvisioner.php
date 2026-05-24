<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\FormationPromotion;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;

class FormationAcademicYearProvisioner
{
    /**
     * @return array{promotions: int, classes: int, skipped: bool, message: string}
     */
    public static function provision(AcademicYear $targetYear, ?AcademicYear $sourceYear): array
    {
        $summary = [
            'promotions' => 0,
            'classes' => 0,
            'skipped' => false,
            'message' => '',
        ];

        if ($sourceYear === null) {
            $summary['message'] = 'Aucune année source avec promotions.';

            return $summary;
        }

        if (SchoolClass::withoutGlobalScopes()
            ->where('academic_year_id', $targetYear->id)
            ->exists()) {
            $summary['skipped'] = true;
            $summary['message'] = 'Des groupes existent déjà pour cette année.';

            return $summary;
        }

        $schoolId = (int) $targetYear->school_id;

        $sourcePromotions = FormationPromotion::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $sourceYear->id)
            ->with('groups')
            ->get();

        if ($sourcePromotions->isEmpty()) {
            $summary['message'] = 'Aucune promotion sur l\'année source.';

            return $summary;
        }

        DB::transaction(function () use ($targetYear, $sourcePromotions, $schoolId, &$summary) {
            foreach ($sourcePromotions as $oldPromo) {
                // Dernière année (L3, M2…) : on ne recrée pas la promo
                if (FormationYearProgression::isFinalYear(
                    $oldPromo->diploma_type,
                    $oldPromo->formation_year
                )) {
                    continue;
                }

                $nextYearLabel = FormationYearProgression::nextYearLabel(
                    $oldPromo->diploma_type,
                    $oldPromo->formation_year
                );

                if ($nextYearLabel === null) {
                    continue;
                }

                $level = FormationLevelResolver::resolve(
                    $schoolId,
                    $nextYearLabel,
                    $oldPromo->filiere,
                    $oldPromo->diploma_type
                );

                $newPromo = FormationPromotion::withoutGlobalScopes()->create([
                    'school_id' => $schoolId,
                    'formation_department_id' => $oldPromo->formation_department_id,
                    'academic_year_id' => $targetYear->id,
                    'name' => $oldPromo->name, // ou adapter le nom si besoin
                    'filiere' => $oldPromo->filiere,
                    'diploma_type' => $oldPromo->diploma_type,
                    'formation_year' => $nextYearLabel,
                    'description' => $oldPromo->description,
                ]);

                $summary['promotions']++;

                $index = 0;
                foreach ($oldPromo->groups as $oldGroup) {
                    $index++;
                    $groupName = $oldGroup->getRawOriginal('name') ?? $oldGroup->name;
                    // Option : regénérer avec FormationGroupNaming::groupName(...)

                    $newGroup = SchoolClass::withoutGlobalScopes()->create([
                        'name' => $groupName,
                        'formation_promotion_id' => $newPromo->id,
                        'promotion_name' => $newPromo->name,
                        'filiere' => $newPromo->filiere,
                        'diploma_type' => $newPromo->diploma_type,
                        'formation_year' => $nextYearLabel,
                        'formation_department_id' => $newPromo->formation_department_id,
                        'academic_year_id' => $targetYear->id,
                        'level_id' => $level->id,
                        'capacity' => $oldGroup->capacity,
                        'room_number' => $oldGroup->room_number,
                        'description' => $oldGroup->description,
                        'school_id' => $schoolId,
                    ]);

                    $summary['classes']++;

                    $subjectIds = $oldGroup->subjects()->pluck('subjects.id');
                    if ($subjectIds->isNotEmpty()) {
                        $newGroup->subjects()->syncWithoutDetaching($subjectIds);
                    }

                    $teacherIds = $oldGroup->teachers()->pluck('users.id');
                    foreach ($teacherIds as $teacherId) {
                        if (! $newGroup->teachers()->where('users.id', $teacherId)->exists()) {
                            $newGroup->teachers()->attach($teacherId);
                        }
                    }
                }
            }
        });

        if ($summary['classes'] === 0) {
            $summary['message'] = 'Aucun groupe créé.';
        } else {
            $summary['message'] = "{$summary['promotions']} promotion(s), {$summary['classes']} groupe(s) créés. Les élèves ne sont pas encore transférés.";
        }

        return $summary;
    }
}
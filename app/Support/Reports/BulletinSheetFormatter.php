<?php

namespace App\Support\Reports;

use App\Models\School;
use App\Services\SchoolBot\BulletinComputation;
use App\Support\SchoolLogoStorage;

/**
 * Normalise les données déjà calculées par BulletinComputation (bulletinData
 * + generalAverage) vers la forme $sheet attendue par
 * resources/views/reports/partials/bulletin-print.blade.php — le "look"
 * pixel-perfect unique du bulletin, partagé par toutes les vues élève et
 * admin. Centraliser ce formatage ici évite que chaque contrôleur
 * réinvente l'arrondi, le libellé de rang ou la ligne de contact école.
 */
class BulletinSheetFormatter
{
    public function __construct(
        private BulletinComputation $bulletinComputation
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $bulletinData  Sortie de BulletinComputation::calculateBulletinData()
     * @param  array{name: string, dob: string, matricule: string}  $student
     * @param  array{rank: ?int, total: int}|null  $rank
     */
    public function format(
        School $school,
        string $academicYearName,
        ?string $periodLabel,
        string $niveau,
        string $classe,
        array $student,
        int $effectif,
        ?array $rank,
        array $bulletinData,
        float $generalAverage,
        float $maxGrade,
        ?string $qrCodeUri = null,
    ): array {
        $maxLabel = $this->formatMaxGrade($maxGrade);

        $rows = collect($bulletinData)->map(fn ($data) => [
            'subject' => $data['subject'],
            'coefficient' => $data['coefficient'],
            'note' => $data['moyenne_matiere'] !== null ? $this->formatNumber((float) $data['moyenne_matiere']) : null,
            'points' => $data['points'] !== null ? $this->formatNumber((float) $data['points']) : null,
            'appreciation' => $data['appreciation'] ?? null,
        ])->values()->all();

        $totalCoef = collect($bulletinData)->sum('coefficient');

        $rangDisplay = '—';
        if ($rank && $rank['rank'] !== null) {
            $rangDisplay = $rank['rank'].' / '.$rank['total'];
        }

        return [
            'school' => [
                'name' => $school->name,
                'motto' => $school->motto ?: 'Excellence – Discipline – Réussite',
                'contactLine' => $this->contactLine($school),
                'logoUri' => SchoolLogoStorage::dataUri($school),
            ],
            'academicYearName' => $academicYearName,
            'periodLabel' => $periodLabel,
            'niveau' => $niveau,
            'classe' => $classe,
            'student' => $student,
            'effectif' => $effectif,
            'rang' => $rangDisplay,
            'moyenneGenerale' => $this->formatNumber($generalAverage),
            'maxLabel' => $maxLabel,
            'rows' => $rows,
            'totalCoef' => $this->formatNumber((float) $totalCoef),
            'appreciationGenerale' => $this->bulletinComputation->generalAppreciationText($generalAverage, $maxGrade),
            'qrCodeUri' => $qrCodeUri,
            'footerQuote' => "L'excellence n'est pas un acte, mais une habitude.",
        ];
    }

    private function contactLine(School $school): ?string
    {
        $parts = array_filter([$school->address, $school->phone, $school->email]);

        return $parts !== [] ? implode(' · ', $parts) : null;
    }

    private function formatNumber(float $value): string
    {
        return number_format($value, 2);
    }

    /** Barème affiché sans zéros inutiles ("10" et non "10.00"). */
    private function formatMaxGrade(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2), '0'), '.');
    }
}

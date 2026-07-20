<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Services\Reports\BulletinReportService;
use App\Support\DashboardAcademicYearContext;
use App\Support\Reports\BulletinSheetFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private BulletinReportService $reports,
        private BulletinSheetFormatter $sheetFormatter
    ) {}

    public function index(Request $request)
    {
        $selectedYear = DashboardAcademicYearContext::resolve($request, 'admin');
        $selectedYearId = $selectedYear?->id;

        $classes = $selectedYearId
            ? SchoolClass::query()
                ->with('level')
                ->where('academic_year_id', $selectedYearId)
                ->orderBy('name')
                ->get()
            : collect();

        return view('admin.reports.index', [
            'selectedYear' => $selectedYear,
            'selectedYearId' => $selectedYearId,
            'classes' => $classes,
            'currentSemester' => $this->reports->currentSemester(),
        ]);
    }

    public function semesterBulletinsPdf(Request $request)
    {
        [$class, $year, $semester] = $this->validatedReportParams($request);

        $bulletins = $this->reports->buildClassSemesterBulletins($class, $year, $semester);

        $sheets = [];
        foreach ($bulletins as $bulletin) {
            $verifyUrl = URL::signedRoute('bulletin.verify', [
                'student_id'       => $bulletin['student_id'],
                'class_id'         => $class->id,
                'academic_year_id' => $year->id,
                'semester'         => $semester,
            ]);

            $sheets[] = $this->sheetFormatter->format(
                school: $class->school,
                academicYearName: $year->name,
                periodLabel: 'Semestre '.$semester,
                niveau: $bulletin['studentInfo']['level'],
                classe: $bulletin['studentInfo']['class'],
                student: [
                    'name' => $bulletin['studentInfo']['name'],
                    'dob' => $bulletin['studentInfo']['date_of_birth'],
                    'matricule' => $bulletin['studentInfo']['identifier'],
                ],
                effectif: $bulletin['rankData']['total'],
                rank: $bulletin['rankData'],
                bulletinData: $bulletin['bulletinData'],
                generalAverage: $bulletin['generalAverage'],
                maxGrade: 20.0,
                qrCodeUri: 'data:image/svg+xml;base64,'.base64_encode(
                    QrCode::format('svg')->size(120)->errorCorrection('H')->generate($verifyUrl)
                ),
            );
        }

        $pdf = Pdf::loadView('reports.pdf.bulletin-sheets', [
            'sheets' => $sheets,
            'documentTitle' => 'Bulletins '.$class->name.' — S'.$semester,
        ])->setPaper('a4', 'portrait');

        $filename = sprintf(
            'bulletins_%s_S%d_%s.pdf',
            $this->slug($class->name),
            $semester,
            $this->slug($year->name)
        );

        return $pdf->download($filename);
    }

    /**
     * Bulletins annuels complets de toute une classe en un seul PDF — pour
     * le primaire (compositions annuelles, pas de semestres, une feuille
     * par élève) comme pour le secondaire (S1 + S2, deux feuilles par
     * élève — il n'existe pas de tableau combiné S1+S2 dans le "look"
     * bulletin), à la différence du "rapport de fin d'année" qui n'est
     * qu'un tableau de synthèse.
     */
    public function annualBulletinsPdf(Request $request)
    {
        [$class, $year] = array_slice($this->validatedReportParams($request, semesterRequired: false), 0, 2);
        $bulletins = $this->reports->buildClassAnnualBulletins($class, $year);

        $sheets = [];
        foreach ($bulletins as $bulletin) {
            $studentArr = [
                'name' => $bulletin['studentInfo']['name'],
                'dob' => $bulletin['studentInfo']['date_of_birth'],
                'matricule' => $bulletin['studentInfo']['identifier'],
            ];

            if ($bulletin['isPrimaire']) {
                $verifyUrl = URL::signedRoute('bulletin.verify', [
                    'student_id'       => $bulletin['student_id'],
                    'class_id'         => $class->id,
                    'academic_year_id' => $year->id,
                ]);

                $sheets[] = $this->sheetFormatter->format(
                    school: $class->school,
                    academicYearName: $year->name,
                    periodLabel: 'Année scolaire complète',
                    niveau: $bulletin['studentInfo']['level'],
                    classe: $bulletin['studentInfo']['class'],
                    student: $studentArr,
                    effectif: count($bulletins),
                    rank: null,
                    bulletinData: $bulletin['semestre1Data']['data'],
                    generalAverage: $bulletin['moyenneAnnuelle'],
                    maxGrade: $bulletin['overallMaxGrade'],
                    qrCodeUri: 'data:image/svg+xml;base64,'.base64_encode(
                        QrCode::format('svg')->size(120)->errorCorrection('H')->generate($verifyUrl)
                    ),
                );
            } else {
                foreach ([1 => 'semestre1Data', 2 => 'semestre2Data'] as $semesterNumber => $key) {
                    $verifyUrl = URL::signedRoute('bulletin.verify', [
                        'student_id'       => $bulletin['student_id'],
                        'class_id'         => $class->id,
                        'academic_year_id' => $year->id,
                        'semester'         => $semesterNumber,
                    ]);

                    $sheets[] = $this->sheetFormatter->format(
                        school: $class->school,
                        academicYearName: $year->name,
                        periodLabel: 'Semestre '.$semesterNumber,
                        niveau: $bulletin['studentInfo']['level'],
                        classe: $bulletin['studentInfo']['class'],
                        student: $studentArr,
                        effectif: count($bulletins),
                        rank: null,
                        bulletinData: $bulletin[$key]['data'],
                        generalAverage: (float) $bulletin[$key]['moyenne'],
                        maxGrade: $bulletin['overallMaxGrade'],
                        qrCodeUri: 'data:image/svg+xml;base64,'.base64_encode(
                            QrCode::format('svg')->size(120)->errorCorrection('H')->generate($verifyUrl)
                        ),
                    );
                }
            }
        }

        $pdf = Pdf::loadView('reports.pdf.bulletin-sheets', [
            'sheets' => $sheets,
            'documentTitle' => 'Bulletins annuels '.$class->name,
        ])->setPaper('a4', 'portrait');

        $filename = sprintf(
            'bulletins_annuels_%s_%s.pdf',
            $this->slug($class->name),
            $this->slug($year->name)
        );

        return $pdf->download($filename);
    }

    /**
     * Relevé d'une seule composition (primaire) pour toute une classe —
     * ex. "toutes les copies de la Composition 2" sans attendre le
     * bulletin annuel complet.
     */
    public function compositionBulletinsPdf(Request $request)
    {
        [$class, $year] = array_slice($this->validatedReportParams($request, semesterRequired: false), 0, 2);

        $composition = (int) $request->validate([
            'composition' => 'required|integer|min:1|max:6',
        ])['composition'];

        $compositionType = 'composition'.$composition;
        $compositionLabel = 'Composition '.$composition;

        $bulletins = $this->reports->buildClassCompositionBulletins($class, $year, $compositionType);

        $sheets = [];
        foreach ($bulletins as $bulletin) {
            $sheets[] = $this->sheetFormatter->format(
                school: $class->school,
                academicYearName: $year->name,
                periodLabel: $compositionLabel,
                niveau: $bulletin['studentInfo']['level'],
                classe: $bulletin['studentInfo']['class'],
                student: [
                    'name' => $bulletin['studentInfo']['name'],
                    'dob' => '-',
                    'matricule' => $bulletin['studentInfo']['identifier'],
                ],
                effectif: count($bulletins),
                rank: null,
                bulletinData: $bulletin['bulletinData'],
                generalAverage: (float) $bulletin['moyenne'],
                maxGrade: $bulletin['overallMaxGrade'],
            );
        }

        $pdf = Pdf::loadView('reports.pdf.bulletin-sheets', [
            'sheets' => $sheets,
            'documentTitle' => $compositionLabel.' — '.$class->name,
        ])->setPaper('a4', 'portrait');

        $filename = sprintf(
            'composition_%d_%s_%s.pdf',
            $composition,
            $this->slug($class->name),
            $this->slug($year->name)
        );

        return $pdf->download($filename);
    }

    public function annualReportPdf(Request $request)
    {
        [$class, $year] = array_slice($this->validatedReportParams($request, semesterRequired: false), 0, 2);
        $rows = $this->reports->buildClassAnnualReport($class, $year);
        $schoolName = config('app.school_name', 'Établissement scolaire');

        $pdf = Pdf::loadView('admin.reports.pdf.annual-class', [
            'rows' => $rows,
            'class' => $class,
            'academicYear' => $year,
            'schoolName' => $schoolName,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        $filename = sprintf(
            'rapport_fin_annee_%s_%s.pdf',
            $this->slug($class->name),
            $this->slug($year->name)
        );

        return $pdf->download($filename);
    }

    public function semesterSummaryCsv(Request $request): StreamedResponse
    {
        [$class, $year, $semester] = $this->validatedReportParams($request);
        $bulletins = $this->reports->buildClassSemesterBulletins($class, $year, $semester);

        $headers = ['Identifiant', 'Élève', 'Classe', 'Semestre', 'Moyenne générale', 'Rang', 'Appréciation globale'];
        $rows = [];
        foreach ($bulletins as $b) {
            $avg = (float) $b['generalAverage'];
            $rank = $b['rankData']['rank'];
            $total = $b['rankData']['total'];
            $rows[] = [
                $b['student']['identifier'],
                $b['student']['name'],
                $class->name,
                'S'.$semester,
                $avg > 0 ? number_format($avg, 2, '.', '') : '',
                $rank ? "{$rank}/{$total}" : '',
                $this->appreciationFromAverage($avg),
            ];
        }

        return $this->csvResponse(
            sprintf('synthese_semestre_%s_S%d_%s.csv', $this->slug($class->name), $semester, $this->slug($year->name)),
            $headers,
            $rows
        );
    }

    public function annualSummaryCsv(Request $request): StreamedResponse
    {
        [$class, $year] = array_slice($this->validatedReportParams($request, semesterRequired: false), 0, 2);
        $rows = $this->reports->buildClassAnnualReport($class, $year);

        $headers = ['Identifiant', 'Élève', 'Moyenne S1', 'Moyenne S2', 'Moyenne annuelle', 'Décision', 'Mention'];
        $data = array_map(fn ($r) => [
            $r['identifier'],
            $r['name'],
            $r['semestre1'] > 0 ? number_format($r['semestre1'], 2, '.', '') : '',
            $r['semestre2'] > 0 ? number_format($r['semestre2'], 2, '.', '') : '',
            $r['moyenne_annuelle'] > 0 ? number_format($r['moyenne_annuelle'], 2, '.', '') : '',
            $r['decision'],
            $r['mention'] ?? '',
        ], $rows);

        return $this->csvResponse(
            sprintf('rapport_fin_annee_%s_%s.csv', $this->slug($class->name), $this->slug($year->name)),
            $headers,
            $data
        );
    }

    public function gradesCsv(Request $request): StreamedResponse
    {
        [$class, $year, $semester] = $this->validatedReportParams($request, semesterRequired: false);
        $exportSemester = $semester;
        $gradeRows = $this->reports->buildGradesExportRows($class, $year, $exportSemester);

        $headers = ['Identifiant', 'Élève', 'Classe', 'Matière', 'Code', 'Semestre', 'Type', 'Note', 'Barème', 'Coefficient', 'Date', 'Commentaire'];
        $data = array_map(fn ($r) => [
            $r['identifiant'],
            $r['eleve'],
            $r['classe'],
            $r['matiere'],
            $r['code_matiere'],
            $r['semestre'],
            $r['type'],
            $r['note'],
            $r['bareme'],
            $r['coefficient'],
            $r['date'],
            $r['appreciation'],
        ], $gradeRows);

        $suffix = $exportSemester ? "_S{$exportSemester}" : '_tous_semestres';

        return $this->csvResponse(
            sprintf('notes_%s%s_%s.csv', $this->slug($class->name), $suffix, $this->slug($year->name)),
            $headers,
            $data
        );
    }

    /**
     * @return array{0: SchoolClass, 1: AcademicYear, 2: int|null}
     */
    private function validatedReportParams(Request $request, bool $semesterRequired = true): array
    {
        $rules = [
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => $semesterRequired ? 'required|in:1,2' : 'nullable|in:1,2',
        ];

        $validated = $request->validate($rules);

        $class = SchoolClass::with(['level', 'school'])->findOrFail($validated['class_id']);
        $year = AcademicYear::findOrFail($validated['academic_year_id']);

        if ((int) $class->academic_year_id !== (int) $year->id) {
            abort(422, 'La classe sélectionnée n\'appartient pas à cette année scolaire.');
        }

        $semester = isset($validated['semester']) ? (int) $validated['semester'] : null;
        if ($semesterRequired && $semester === null) {
            $semester = $this->reports->currentSemester();
        }

        return [$class, $year, $semester];
    }

    private function appreciationFromAverage(float $avg): string
    {
        if ($avg <= 0) {
            return '';
        }
        if ($avg >= 16) {
            return 'Très Bien';
        }
        if ($avg >= 14) {
            return 'Bien';
        }
        if ($avg >= 12) {
            return 'Assez Bien';
        }
        if ($avg >= 10) {
            return 'Passable';
        }
        if ($avg >= 8) {
            return 'Insuffisant';
        }

        return 'Très Insuffisant';
    }

    private function slug(string $value): string
    {
        $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $value) ?? 'export';

        return trim($slug, '_') ?: 'export';
    }

    /**
     * @param  list<string>  $headers
     * @param  list<array<int, string|int|float|null>>  $rows
     */
    private function csvResponse(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

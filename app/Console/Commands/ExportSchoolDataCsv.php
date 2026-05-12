<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnexpectedValueException;

class ExportSchoolDataCsv extends Command
{
    protected $signature = 'school:export-csv
                            {--dir=exports/botpress-csv : Sous-chemin sous storage/app (les fichiers y sont créés)}
                            {--tables= : Liste de tables séparée par virgules (sans espaces)}
                            {--skip-bom : Ne pas préfixer les CSV par le BOM UTF-8 (pour outils autres qu’Excel)}
                            {--chunk=5000 : Nombre de lignes lues à la fois sur les grosses tables}';

    protected $description = 'Exporte des tables en CSV dans storage/app (usage : documents / Knowledge Base Botpress)';

    /**
     * Tables « métier » par défaut ; les tables Laravel système/cache/jobs sont exclues.
     *
     * @var list<string>
     */
    private function defaultTables(): array
    {
        return [
            'academic_years',
            'levels',
            'subjects',
            'classes',
            'class_teacher',
            'class_subject',
            'teacher_subjects',
            'teacher_assignments',
            'assignments',
            'grades',
            'attendances',
            'schedules',
            'events',
            'timetables',
        ];
    }

    public function handle(): int
    {
        $sub = trim((string) $this->option('dir'), '/');
        if ($sub === '') {
            throw new UnexpectedValueException('L’option --dir ne peut pas être vide.');
        }

        $destination = storage_path('app/'.$sub);

        if (! is_dir($destination) && ! mkdir($destination, 0755, true) && ! is_dir($destination)) {
            $this->error("Impossible de créer : {$destination}");

            return self::FAILURE;
        }

        $tablesOption = trim((string) $this->option('tables'));
        $tables = $tablesOption !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $tablesOption))))
            : array_merge($this->defaultTables(), ['users']);

        sort($tables);
        $tables = array_values(array_unique($tables));

        $useBom = ! $this->option('skip-bom');
        $chunkSize = max(500, min(50000, (int) $this->option('chunk')));

        $this->info("Dossier de sortie : {$destination}");

        foreach ($tables as $table) {
            if ($table === 'users') {
                $this->exportUsersSafeCsv($destination, $useBom);
                continue;
            }

            if (! Schema::hasTable($table)) {
                $this->warn("Table ignorée (inexistante) : {$table}");
                continue;
            }

            $this->exportTableCsv($destination, $table, $chunkSize, $useBom);
            $this->line("  ✓ {$table}.csv");
        }

        $this->newLine();
        $this->line('Créé : users_csv_readme.txt');
        file_put_contents(
            $destination.DIRECTORY_SEPARATOR.'users_csv_readme.txt',
            <<<TXT
Contenu CSV (users_school_bot.csv) : colonnes NON sensibles pour un bot public.
Exclut : password, remember_token, email (données personnelles).
À ne pas diffuser publiquement si votre politique impose l’anonymisation des noms.
TXT
        );

        return self::SUCCESS;
    }

    private function exportUsersSafeCsv(string $destination, bool $useBom): void
    {
        if (! Schema::hasTable('users')) {
            $this->warn('Table users absente.');

            return;
        }

        $filename = $destination.DIRECTORY_SEPARATOR.'users_school_bot.csv';
        $handle = fopen($filename, 'w');
        if ($handle === false) {
            $this->error("Écriture impossible : {$filename}");

            return;
        }

        if ($useBom) {
            fwrite($handle, "\xEF\xBB\xBF");
        }

        $headers = ['id', 'name', 'identifier', 'role', 'status', 'class_id', 'desired_class', 'created_at', 'updated_at'];
        fputcsv($handle, $headers);

        foreach (DB::table('users')->orderBy('id')->cursor() as $row) {
            $r = (array) $row;
            $line = [];
            foreach ($headers as $h) {
                $line[] = $r[$h] ?? null;
            }
            fputcsv($handle, $line);
        }

        fclose($handle);
        $this->line('  ✓ users_school_bot.csv (sans email / password)');
    }

    private function exportTableCsv(string $destination, string $table, int $chunkSize, bool $useBom): void
    {
        $columns = Schema::getColumnListing($table);
        if ($columns === []) {
            return;
        }

        $filename = $destination.DIRECTORY_SEPARATOR.$table.'.csv';
        $handle = fopen($filename, 'w');
        if ($handle === false) {
            $this->error("Écriture impossible : {$filename}");

            return;
        }

        if ($useBom) {
            fwrite($handle, "\xEF\xBB\xBF");
        }

        fputcsv($handle, $columns);

        $orderColumn = $columns[0];

        DB::table($table)->orderBy($orderColumn)->lazy($chunkSize)->each(function ($row) use ($handle, $columns): void {
            $r = (array) $row;
            $line = [];
            foreach ($columns as $c) {
                $line[] = $r[$c] ?? null;
            }
            fputcsv($handle, $line);
        });

        fclose($handle);
    }
}

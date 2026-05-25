<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Support\LoadTest\LoadTestDataPurge;
use App\Support\LoadTest\LoadTestSchoolFactory;
use Illuminate\Console\Command;

class RecreateLoadTestSchools extends Command
{
    protected $signature = 'schools:recreate-load-test
                            {slugs?* : Slugs établissement (défaut : ep-blaise-diagne, ecole-seydou-nourou-tall)}';

    protected $description = 'Supprime et recrée des établissements du jeu de données charge';

    /** @var array<string, array<string, mixed>> */
    private const DEFINITIONS = [
        'ep-blaise-diagne' => [
            'slug' => 'ep-blaise-diagne',
            'name' => 'École primaire Blaise Diagne',
            'type' => School::TYPE_PRIMAIRE,
            'city' => 'Dakar',
            'address' => 'Médina, Dakar',
            'admin_email' => 'admin@ep-blaise-diagne.edu.sn',
            'seed' => 'classic',
        ],
        'ecole-seydou-nourou-tall' => [
            'slug' => 'ecole-seydou-nourou-tall',
            'name' => 'École Mixte Seydou Nourou Tall',
            'type' => School::TYPE_MIXTE,
            'city' => 'Thiès',
            'admin_email' => 'admin@ecole-seydou-nourou-tall.edu.sn',
            'seed' => 'classic',
        ],
    ];

    public function handle(): int
    {
        $slugs = $this->argument('slugs') ?: ['ep-blaise-diagne', 'ecole-seydou-nourou-tall'];
        $factory = (new LoadTestSchoolFactory)->setOutput($this->output);

        foreach ($slugs as $slug) {
            if (! isset(self::DEFINITIONS[$slug])) {
                $this->error("Slug inconnu : {$slug}");

                continue;
            }

            $def = self::DEFINITIONS[$slug];
            $existing = School::query()->where('slug', $slug)->first();

            if ($existing) {
                $this->warn("Suppression de {$existing->name} (ID {$existing->id})…");
                LoadTestDataPurge::forSchool($existing->id);
            }

            $this->info("Création de {$def['name']}…");
            $factory->seedClassic($def);
        }

        $this->newLine();
        $this->info('Terminé. Primaire : un instituteur pour toutes les matières (CI → CM2).');

        return self::SUCCESS;
    }
}

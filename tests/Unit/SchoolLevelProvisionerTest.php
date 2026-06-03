<?php

namespace Tests\Unit;

use App\Models\School;
use App\Support\SchoolLevelProvisioner;
use PHPUnit\Framework\TestCase;

class SchoolLevelProvisionerTest extends TestCase
{
    public function test_mixte_definitions_include_primaire_and_college_lycee(): void
    {
        $school = new School(['establishment_type' => School::TYPE_MIXTE]);

        $definitions = SchoolLevelProvisioner::definitionsForSchool($school);
        $names = array_column($definitions, 'name');

        $this->assertContains('CI', $names);
        $this->assertContains('CM2', $names);
        $this->assertContains('6ème', $names);
        $this->assertContains('Terminale', $names);
    }

    public function test_primaire_definitions_only_primary_levels(): void
    {
        $school = new School(['establishment_type' => School::TYPE_PRIMAIRE]);

        $definitions = SchoolLevelProvisioner::definitionsForSchool($school);

        $this->assertCount(6, $definitions);
        $this->assertSame('primaire', $definitions[0]['cycle']);
        $this->assertSame('CM2', $definitions[5]['name']);
    }

    public function test_college_definitions_only_college_levels(): void
    {
        $definitions = SchoolLevelProvisioner::definitionsForType(School::TYPE_COLLEGE);
        $names = array_column($definitions, 'name');

        $this->assertCount(4, $definitions);
        $this->assertSame(['6ème', '5ème', '4ème', '3ème'], $names);
    }

    public function test_lycee_definitions_only_lycee_levels(): void
    {
        $definitions = SchoolLevelProvisioner::definitionsForType(School::TYPE_LYCEE);
        $names = array_column($definitions, 'name');

        $this->assertCount(3, $definitions);
        $this->assertSame(['Seconde', 'Première', 'Terminale'], $names);
    }

    public function test_formation_has_no_default_levels(): void
    {
        $this->assertSame([], SchoolLevelProvisioner::definitionsForType(School::TYPE_FORMATION));
    }

    public function test_default_levels_hint_lists_levels_for_primaire(): void
    {
        $hint = SchoolLevelProvisioner::defaultLevelsHintForType(School::TYPE_PRIMAIRE);

        $this->assertStringContainsString('CI', $hint);
        $this->assertStringContainsString('CM2', $hint);
        $this->assertStringNotContainsString('6ème', $hint);
    }
}

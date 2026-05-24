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
}

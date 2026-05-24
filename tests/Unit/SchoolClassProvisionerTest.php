<?php

namespace Tests\Unit;

use App\Models\Level;
use App\Support\SchoolClassProvisioner;
use PHPUnit\Framework\TestCase;

class SchoolClassProvisionerTest extends TestCase
{
    public function test_default_class_name_matches_level_name(): void
    {
        $level = new Level(['name' => 'CM2', 'cycle' => 'primaire', 'order' => 6]);

        $this->assertSame('CM2', SchoolClassProvisioner::defaultClassNameForLevel($level));

        $sixieme = new Level(['name' => '6ème', 'cycle' => 'college', 'order' => 1]);
        $this->assertSame('6ème', SchoolClassProvisioner::defaultClassNameForLevel($sixieme));
    }

    public function test_class_cycles_include_primaire(): void
    {
        $this->assertContains('primaire', SchoolClassProvisioner::CLASS_CYCLES);
        $this->assertContains('college', SchoolClassProvisioner::CLASS_CYCLES);
        $this->assertContains('lycee', SchoolClassProvisioner::CLASS_CYCLES);
    }
}

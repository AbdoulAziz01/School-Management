<?php

namespace Tests\Unit;

use App\Models\SchoolClass;
use PHPUnit\Framework\TestCase;

class SchoolClassNameTest extends TestCase
{
    public function test_primaire_level_names_are_not_converted_to_cea_style(): void
    {
        $class = new SchoolClass();
        $class->setRawAttributes(['name' => 'CE1']);

        $this->assertSame('CE1', $class->name);
        $this->assertSame('CM2', (new SchoolClass(['name' => 'CM2']))->name);
    }

    public function test_section_suffix_still_converts_number_to_letter(): void
    {
        $class = new SchoolClass();
        $class->setRawAttributes(['name' => '6ème 1']);

        $this->assertSame('6ème A', $class->name);
    }

    public function test_primaire_section_names_preserved(): void
    {
        $class = new SchoolClass();
        $class->setRawAttributes(['name' => 'CE1 A']);

        $this->assertSame('CE1 A', $class->name);
    }
}

<?php

namespace Tests\Unit;

use App\Models\School;
use PHPUnit\Framework\TestCase;

class SchoolGradingModeTest extends TestCase
{
    public function test_formation_without_lmd_uses_classic_grading(): void
    {
        $school = new School([
            'establishment_type' => School::TYPE_FORMATION,
            'formation_use_lmd' => false,
        ]);

        $this->assertTrue($school->isFormation());
        $this->assertFalse($school->usesLmdGrading());
        $this->assertTrue($school->usesClassicGrading());
        $this->assertTrue($school->supportsAutomaticClassPromotion());
    }

    public function test_formation_with_lmd_skips_class_promotion(): void
    {
        $school = new School([
            'establishment_type' => School::TYPE_FORMATION,
            'formation_use_lmd' => true,
        ]);

        $this->assertTrue($school->usesLmdGrading());
        $this->assertFalse($school->supportsAutomaticClassPromotion());
    }
}

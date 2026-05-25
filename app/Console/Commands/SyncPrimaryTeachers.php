<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use App\Support\PrimaryClassTeacherProvisioner;
use Illuminate\Console\Command;

class SyncPrimaryTeachers extends Command
{
    protected $signature = 'schools:sync-primary-teachers {--school= : ID établissement (sinon tous)}';

    protected $description = 'Primaire : un instituteur unique par établissement pour toutes les matières et classes CI–CM2';

    public function handle(): int
    {
        $query = School::query();
        if ($id = $this->option('school')) {
            $query->where('id', $id);
        }

        $schools = $query->get()->filter(fn (School $s) => PrimaryClassTeacherProvisioner::schoolHasPrimaireCycle($s));

        if ($schools->isEmpty()) {
            $this->warn('Aucun établissement avec cycle primaire.');

            return self::SUCCESS;
        }

        foreach ($schools as $school) {
            PrimaryClassTeacherProvisioner::ensurePrimaireSubjects($school->id);
            $teacher = PrimaryClassTeacherProvisioner::ensurePrimaryTeacher($school);

            $classes = SchoolClass::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->whereHas('level', fn ($q) => $q->where('cycle', 'primaire'))
                ->with('level.subjects')
                ->get();

            $primaireClassIds = $classes->pluck('id');

            TeacherAssignment::withoutGlobalScopes()
                ->whereIn('class_id', $primaireClassIds)
                ->where('teacher_id', '!=', $teacher->id)
                ->delete();

            foreach ($classes as $class) {
                PrimaryClassTeacherProvisioner::assignToClass($class, $teacher);
            }

            $this->info("{$school->name} : instituteur {$teacher->email} → {$classes->count()} classe(s) primaire");
        }

        return self::SUCCESS;
    }
}

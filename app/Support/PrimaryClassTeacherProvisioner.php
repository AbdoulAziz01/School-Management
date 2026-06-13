<?php

namespace App\Support;

use App\Models\Level;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Primaire : un instituteur unique pour toutes les matières et classes du cycle primaire.
 */
class PrimaryClassTeacherProvisioner
{
    /** @var array<string, array{0: string, 1: string, 2: int}> */
    public const SUBJECT_CATALOG = [
        'Français' => ['FR-P', 'Lettres', 8],
        'Mathématiques' => ['MATH-P', 'Sciences', 6],
        'Éveil scientifique' => ['EV', 'Sciences', 3],
        'EPS' => ['EPS-P', 'EPS', 2],
        'Anglais' => ['ANG-P', 'Langues vivantes', 2],
    ];

    public static function schoolHasPrimaireCycle(School $school): bool
    {
        if ($school->isPrimaireEstablishment() || $school->isMixte()) {
            return true;
        }

        return Level::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('cycle', 'primaire')
            ->exists();
    }

    public static function isPrimaireLevel(?Level $level): bool
    {
        return $level?->cycle === 'primaire';
    }

    /** @return list<string> */
    public static function primaireSubjectCodes(): array
    {
        return array_values(array_map(fn (array $m) => $m[0], self::SUBJECT_CATALOG));
    }

    public static function ensurePrimaireSubjects(int $schoolId): void
    {
        $subjects = [];
        foreach (self::SUBJECT_CATALOG as $name => [$code, $dept, $hours]) {
            $subjects[$name] = Subject::withoutGlobalScopes()->firstOrCreate(
                ['school_id' => $schoolId, 'code' => $code],
                [
                    'name' => $name,
                    'coefficient' => 1,
                    'department' => $dept,
                    'is_active' => true,
                    'hours_per_week' => $hours,
                    'is_core_subject' => true,
                ]
            );
        }

        $primaireLevels = Level::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('cycle', 'primaire')
            ->get();

        foreach ($primaireLevels as $level) {
            foreach ($subjects as $subject) {
                if (! $level->subjects()->where('subjects.id', $subject->id)->exists()) {
                    $level->subjects()->attach($subject->id, [
                        'coefficient' => in_array($subject->name, ['Français', 'Mathématiques'], true) ? 4 : 2,
                        'is_compulsory' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Crée ou récupère l'instituteur unique du primaire pour l'établissement.
     */
    public static function ensurePrimaryTeacher(School $school): User
    {
        $email = 'instituteur.primaire@'.$school->slug.'.edu.sn';

        $existing = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('role', User::ROLE_TEACHER)
            ->where('email', $email)
            ->first();

        if ($existing) {
            self::attachAllPrimaireSubjects($school, $existing);

            return $existing;
        }

        $fn = 'Aminata';
        $ln = 'Diop';
        $teacher = (new User)->forceFill([
            'school_id' => $school->id,
            'name' => 'Instituteur·trice — '.$fn.' '.$ln,
            'email' => $email,
            'password' => Hash::make((string) config('school.load_test_default_password', 'password')),
            'identifier' => 'INST-P-'.$school->id,
            'user_id' => 'INST-P-'.$school->id,
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_APPROVED,
            'email_verified_at' => now(),
        ]);
        $teacher->save();

        self::attachAllPrimaireSubjects($school, $teacher);

        return $teacher;
    }

    public static function assignToClass(SchoolClass $class, User $teacher): void
    {
        $level = $class->level;
        if (! self::isPrimaireLevel($level)) {
            return;
        }

        $class->teachers()->syncWithoutDetaching([$teacher->id]);

        foreach ($level->subjects as $subject) {
            $class->subjects()->syncWithoutDetaching([$subject->id]);
            TeacherAssignment::withoutGlobalScopes()->firstOrCreate([
                'teacher_id' => $teacher->id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $class->academic_year_id,
            ], [
                'school_id' => $class->school_id,
            ]);
        }
    }

    private static function attachAllPrimaireSubjects(School $school, User $teacher): void
    {
        self::ensurePrimaireSubjects($school->id);

        $subjectIds = Subject::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->whereIn('code', self::primaireSubjectCodes())
            ->pluck('id');

        if ($subjectIds->isNotEmpty()) {
            $teacher->subjects()->syncWithoutDetaching($subjectIds);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Services\SchoolBot\SchoolBotStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class SchoolBotController extends Controller
{
    /** Les agrégats (stats/repeaters/outcomes) sont coûteux à recalculer et
     *  interrogés en rafale par le bot dans une même conversation ; un TTL
     *  court évite de tout recalculer à chaque question sans risquer des
     *  données trop périmées. */
    private const STATS_CACHE_TTL_SECONDS = 60;

    public function __construct(
        private SchoolBotStatsService $statsService
    ) {}

    public function stats(Request $request): JsonResponse
    {
        $schoolId = $this->resolveSchoolId($request);

        $data = Cache::remember(
            "school-bot:stats:{$schoolId}",
            self::STATS_CACHE_TTL_SECONDS,
            fn () => $this->statsService->stats($schoolId)
        );

        return response()->json($data);
    }

    public function repeaters(Request $request): JsonResponse
    {
        $schoolId = $this->resolveSchoolId($request);

        $limit = (int) $request->query('limit', 50);
        $limit = max(1, min($limit, 200));

        $items = Cache::remember(
            "school-bot:repeaters:{$schoolId}:{$limit}",
            self::STATS_CACHE_TTL_SECONDS,
            fn () => $this->statsService->repeatersList($schoolId, $limit)
        );

        return response()->json([
            'items' => $items,
            'hint' => 'Liste basée sur l’heuristique « années académiques distinctes avec notes ».',
        ]);
    }

    public function outcomes(Request $request): JsonResponse
    {
        $schoolId = $this->resolveSchoolId($request);

        $data = Cache::remember(
            "school-bot:outcomes:{$schoolId}",
            self::STATS_CACHE_TTL_SECONDS,
            fn () => $this->statsService->outcomes($schoolId)
        );

        return response()->json($data);
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $schoolId = $this->resolveSchoolId($request);

        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['message' => 'Le paramètre « q » est requis.'], 422);
        }
        if (strlen($q) > 200) {
            return response()->json(['message' => 'Requête trop longue (max 200 caractères).'], 422);
        }

        $items = $this->statsService->studentSearchQuery($q, $schoolId)->limit(25)->get()
            ->map(fn (User $u) => $this->studentSummary($u));

        return response()->json([
            'query' => $q,
            'items' => $items,
        ]);
    }

    /**
     * Élèves et enseignants (ex. identifiants E… / P…), sans exposer l’email.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $schoolId = $this->resolveSchoolId($request);

        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['message' => 'Le paramètre « q » est requis.'], 422);
        }
        if (strlen($q) > 200) {
            return response()->json(['message' => 'Requête trop longue (max 200 caractères).'], 422);
        }

        $items = $this->statsService->userSearchQuery($q, $schoolId)->limit(25)->get()
            ->map(fn (User $u) => $this->userSummaryForBot($u));

        return response()->json([
            'query' => $q,
            'items' => $items,
        ]);
    }

    public function showStudent(Request $request, int $id): JsonResponse
    {
        $schoolId = $this->resolveSchoolId($request);

        $user = User::query()
            ->where('school_id', $schoolId)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->with(['class:id,name,level_id,academic_year_id', 'class.level:id,name', 'class.academicYear:id,name'])
            ->find($id);

        if ($user === null) {
            return response()->json(['message' => 'Élève introuvable.'], 404);
        }

        return response()->json($this->studentDetail($user));
    }

    /**
     * Résout et valide l'établissement ciblé par la requête bot. Le secret Bearer
     * (EnsureSchoolBotSecret) authentifie l'appelant mais n'identifie aucun
     * établissement précis — school_id doit donc être fourni explicitement à
     * chaque appel pour éviter d'agréger les données de toute la plateforme.
     */
    private function resolveSchoolId(Request $request): int
    {
        $validated = $request->validate([
            'school_id' => ['required', 'integer'],
        ]);

        $exists = School::query()
            ->where('id', $validated['school_id'])
            ->where('is_active', true)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'school_id' => 'Établissement introuvable ou inactif.',
            ]);
        }

        return (int) $validated['school_id'];
    }

    /**
     * @return array<string, mixed>
     */
    private function studentSummary(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'identifier' => $u->identifier,
            'status' => $u->status,
            'class' => $u->class?->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function userSummaryForBot(User $u): array
    {
        $row = [
            'id' => $u->id,
            'name' => $u->name,
            'identifier' => $u->identifier,
            'role' => $u->role,
            'status' => $u->status,
        ];
        if ($u->isStudent()) {
            $row['class'] = $u->class?->name;
        }

        return $row;
    }

    /**
     * @return array<string, mixed>
     */
    private function studentDetail(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'identifier' => $u->identifier,
            'status' => $u->status,
            'desired_class' => $u->desired_class,
            'class' => $u->class ? [
                'id' => $u->class->id,
                'name' => $u->class->name,
                'level' => $u->class->level?->name,
                'academic_year' => $u->class->academicYear?->name,
            ] : null,
        ];
    }
}

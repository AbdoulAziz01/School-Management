<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SchoolBot\SchoolBotStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolBotController extends Controller
{
    public function __construct(
        private SchoolBotStatsService $statsService
    ) {}

    public function stats(): JsonResponse
    {
        return response()->json($this->statsService->stats());
    }

    public function repeaters(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 50);
        $limit = max(1, min($limit, 200));

        return response()->json([
            'items' => $this->statsService->repeatersList($limit),
            'hint' => 'Liste basée sur l’heuristique « années académiques distinctes avec notes ».',
        ]);
    }

    public function outcomes(): JsonResponse
    {
        return response()->json($this->statsService->outcomes());
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['message' => 'Le paramètre « q » est requis.'], 422);
        }
        if (strlen($q) > 200) {
            return response()->json(['message' => 'Requête trop longue (max 200 caractères).'], 422);
        }

        $items = $this->statsService->studentSearchQuery($q)->limit(25)->get()
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
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['message' => 'Le paramètre « q » est requis.'], 422);
        }
        if (strlen($q) > 200) {
            return response()->json(['message' => 'Requête trop longue (max 200 caractères).'], 422);
        }

        $items = $this->statsService->userSearchQuery($q)->limit(25)->get()
            ->map(fn (User $u) => $this->userSummaryForBot($u));

        return response()->json([
            'query' => $q,
            'items' => $items,
        ]);
    }

    public function showStudent(int $id): JsonResponse
    {
        $user = User::query()
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->with(['class:id,name,level_id,academic_year_id', 'class.level:id,name', 'class.academicYear:id,name'])
            ->find($id);

        if ($user === null) {
            return response()->json(['message' => 'Élève introuvable.'], 404);
        }

        return response()->json($this->studentDetail($user));
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

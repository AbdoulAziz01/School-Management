<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StudentSearch
{
    public static function baseQuery(): Builder
    {
        return User::query()
            ->whereIn('role', User::ROLE_STUDENT_ALIASES);
    }

    public static function apply(Builder $query, string $search): Builder
    {
        $search = trim($search);

        if ($search === '') {
            return $query;
        }

        $words = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($words as $word) {
            if (mb_strlen($word) < 1) {
                continue;
            }

            $term = '%'.addcslashes($word, '%_\\').'%';

            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'ilike', $term)
                    ->orWhere('identifier', 'ilike', $term)
                    ->orWhere('email', 'ilike', $term);
            });
        }

        return $query;
    }

    /**
     * Suggestions pour autocomplétion ou recherche approximative.
     *
     * @return Collection<int, User>
     */
    public static function suggestions(string $search, int $limit = 8): Collection
    {
        $search = trim($search);

        if (mb_strlen($search) < 2) {
            return collect();
        }

        $exact = self::baseQuery()
            ->with('class')
            ->tap(fn (Builder $q) => self::apply($q, $search))
            ->orderBy('name')
            ->limit($limit)
            ->get();

        if ($exact->count() >= $limit) {
            return $exact;
        }

        $needle = mb_strtolower($search);
        $excludeIds = $exact->pluck('id')->all();

        $prefixTerm = addcslashes($search, '%_\\').'%';
        $prefixMatches = self::baseQuery()
            ->with('class')
            ->whereNotIn('id', $excludeIds)
            ->where('name', 'ilike', $prefixTerm)
            ->orderBy('name')
            ->limit($limit - $exact->count())
            ->get();

        $results = $exact->concat($prefixMatches)->unique('id');

        if ($results->count() >= $limit) {
            return $results->take($limit)->values();
        }

        $excludeIds = $results->pluck('id')->all();
        $needleLen = mb_strlen($needle);

        $candidates = self::baseQuery()
            ->with('class')
            ->when($excludeIds !== [], fn (Builder $q) => $q->whereNotIn('id', $excludeIds))
            ->orderBy('name')
            ->limit(400)
            ->get();

        $fuzzy = $candidates
            ->map(function (User $user) use ($needle, $needleLen) {
                $hay = mb_strtolower($user->name);
                $score = 0;

                similar_text($needle, $hay, $fullPct);
                $score = max($score, (int) $fullPct);

                foreach (preg_split('/\s+/u', $hay, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $part) {
                    if (str_starts_with($part, $needle)) {
                        $score = max($score, 90);
                    } elseif (str_contains($part, $needle)) {
                        $score = max($score, 80);
                    } elseif ($needleLen >= 3) {
                        similar_text($needle, $part, $partPct);
                        $score = max($score, (int) $partPct);
                    }
                }

                if ($user->identifier && str_contains(mb_strtolower($user->identifier), $needle)) {
                    $score = max($score, 85);
                }

                return ['user' => $user, 'score' => $score];
            })
            ->filter(fn (array $row) => $row['score'] >= 50)
            ->sortByDesc('score')
            ->take($limit - $results->count())
            ->pluck('user');

        return $results->concat($fuzzy)->unique('id')->take($limit)->values();
    }
}

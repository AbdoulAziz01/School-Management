<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolBotSecret
{
    /**
     * Vérifie le secret Bearer configuré pour l’API Botpress (school bot).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.school_bot.secret');

        if ($secret === null || $secret === '') {
            return response()->json([
                'message' => 'School bot API is not configured.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $authorization = $request->header('Authorization', '');
        if (! is_string($authorization) || ! str_starts_with($authorization, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        $token = trim(substr($authorization, 7));
        if ($token === '' || ! hash_equals($secret, $token)) {
            return response()->json(['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}

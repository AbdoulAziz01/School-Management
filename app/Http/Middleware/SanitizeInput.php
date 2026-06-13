<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    private const SKIP_FIELDS = ['password', 'password_confirmation', 'current_password'];

    public function handle(Request $request, Closure $next): Response
    {
        $sanitized = $this->clean($request->all());
        $request->merge($sanitized);

        return $next($request);
    }

    private function clean(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, self::SKIP_FIELDS, true)) {
                continue;
            }

            $data[$key] = match (true) {
                is_string($value) => strip_tags($value),
                is_array($value)  => $this->clean($value),
                default           => $value,
            };
        }

        return $data;
    }
}

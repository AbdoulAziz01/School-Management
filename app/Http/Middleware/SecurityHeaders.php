<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    private const CDN_JSDELIVR   = 'https://cdn.jsdelivr.net';
    private const CDN_CLOUDFLARE = 'https://cdnjs.cloudflare.com';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $csp = implode('; ', [
            "default-src 'self'",
            // CDN JS autorisés (Bootstrap, Chart.js, Select2, FullCalendar, Axios)
            "script-src 'self' 'unsafe-inline' " . self::CDN_JSDELIVR,
            // CDN CSS + inline <style> autorisés (layouts utilisent des blocs <style>)
            "style-src 'self' 'unsafe-inline' " . self::CDN_JSDELIVR . ' ' . self::CDN_CLOUDFLARE,
            // Font Awesome (woff2 servis depuis cdnjs)
            "font-src 'self' " . self::CDN_CLOUDFLARE . ' data:',
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "media-src 'none'",
            "object-src 'none'",
            "frame-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
            "block-all-mixed-content",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}

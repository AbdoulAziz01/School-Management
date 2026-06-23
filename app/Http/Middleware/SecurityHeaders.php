<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    private const CDN_JSDELIVR    = 'https://cdn.jsdelivr.net';
    private const CDN_CLOUDFLARE  = 'https://cdnjs.cloudflare.com';
    private const CDN_GOOGLE_FONT = 'https://fonts.googleapis.com';
    private const CDN_GSTATIC     = 'https://fonts.gstatic.com';
    private const AI_AGENT_URL    = 'http://localhost:3000';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $csp = implode('; ', [
            "default-src 'self'",
            // CDN JS autorisés (Bootstrap, Chart.js, Select2, FullCalendar, Axios)
            "script-src 'self' 'unsafe-inline' " . self::CDN_JSDELIVR,
            // CDN CSS + inline <style> autorisés + Google Fonts
            "style-src 'self' 'unsafe-inline' " . self::CDN_JSDELIVR . ' ' . self::CDN_CLOUDFLARE . ' ' . self::CDN_GOOGLE_FONT,
            // Font Awesome (cdnjs) + Google Fonts (gstatic)
            "font-src 'self' " . self::CDN_CLOUDFLARE . ' ' . self::CDN_GSTATIC . ' data:',
            "img-src 'self' data: blob:",
            // jsDelivr + Cloudflare (pour le précache Service Worker) + agent IA Node.js local
            "connect-src 'self' " . self::CDN_JSDELIVR . ' ' . self::CDN_CLOUDFLARE . ' ' . self::CDN_GOOGLE_FONT . ' ' . self::CDN_GSTATIC . ' ' . self::AI_AGENT_URL,
            // Service Worker servi depuis l'origine
            "worker-src 'self'",
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

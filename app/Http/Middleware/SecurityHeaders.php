<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser-side hardening on every response. The CSP is applied only to the
 * public site: the Filament panel ships its own inline scripts and styles
 * and would need a nonce scheme to be locked down the same way.
 */
class SecurityHeaders
{
    /** Kept as a constant so the site layout can repeat it in a <meta> tag. */
    public const SITE_CSP = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://maps.googleapis.com https://maps.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: blob: https://*.googleapis.com https://*.gstatic.com https://*.ggpht.com; connect-src 'self' https://maps.googleapis.com; frame-src 'none'; object-src 'none'; base-uri 'self'; form-action 'self' https://wa.me; upgrade-insecure-requests";

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(), microphone=(), payment=(), usb=()');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        if ($request->isSecure() || app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if ($this->isPublicSite($request)) {
            $response->headers->set('Content-Security-Policy', self::SITE_CSP);
        }

        return $response;
    }

    private function isPublicSite(Request $request): bool
    {
        $path = '/'.ltrim($request->path(), '/');

        return ! str_starts_with($path, '/admin') && ! str_starts_with($path, '/api') && ! str_starts_with($path, '/livewire');
    }
}

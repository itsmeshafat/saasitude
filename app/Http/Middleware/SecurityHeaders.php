<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers on every response (web + API).
 *
 * Deliberately NO strict Content-Security-Policy yet: Tyro Dashboard/Tyro Login
 * ship inline <script> blocks and inline styles across many views, so a
 * nonce/hash-based CSP is a larger migration (tracked separately). The headers
 * below are safe to apply globally today.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Clickjacking: the app is never legitimately framed (no embeddable
        // widgets); frame-ancestors would need CSP, so DENY covers all frames.
        $response->headers->set('X-Frame-Options', 'DENY');

        // Stop MIME-sniffing (defense in depth alongside correct Content-Type).
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Never leak full URLs (which can contain checkout/tokens) cross-origin.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable browser features the app does not use.
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // HSTS only makes sense once TLS is terminated in front of the app;
        // behind Laragon HTTP it would pin a broken policy.
        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}

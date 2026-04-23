<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Nonce used for inline scripts — generated once per request.
     */
    private string $nonce;

    public function __construct()
    {
        $this->nonce = base64_encode(random_bytes(16));
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Share nonce with Blade so views can use @nonce / cspNonce()
        app()->instance('csp-nonce', $this->nonce);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(self), payment=()');

        // HSTS — only set over HTTPS
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content-Security-Policy
        // camera=(self) is needed for the check-in QR scanner
        $csp = implode('; ', array_filter([
            "default-src 'self'",
            "script-src 'self' 'nonce-{$this->nonce}' https://cdn.jsdelivr.net https://checkout.flutterwave.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob: https:",
            "connect-src 'self'" . ($this->isDev() ? " ws://localhost:* http://localhost:*" : ''),
            "frame-src https://checkout.flutterwave.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests",
        ]));

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }

    private function isDev(): bool
    {
        return app()->environment('local', 'development');
    }
}

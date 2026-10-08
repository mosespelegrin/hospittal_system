<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // The public site gets a strict nonce-based CSP. The Filament admin
        // panel injects its own inline scripts/styles, so it is exempt from CSP
        // (but still receives the other headers).
        $csp = ! $request->is('admin', 'admin/*', 'livewire/*', 'livewire-*/*');

        if ($csp) {
            $nonce = Str::random(32);
            Vite::useCspNonce($nonce);
            $request->attributes->set('csp_nonce', $nonce);
        }

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if ($csp) {
            $nonce = $request->attributes->get('csp_nonce');
            $dev = app()->environment('local') ? ' http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173' : '';

            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}'{$dev}",
                "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com{$dev}",
                "font-src 'self' https://fonts.gstatic.com",
                "img-src 'self' data:",
                "connect-src 'self'{$dev}",
                "form-action 'self'",
                "base-uri 'self'",
                "frame-ancestors 'self'",
                "object-src 'none'",
            ]));
        }

        return $response;
    }
}

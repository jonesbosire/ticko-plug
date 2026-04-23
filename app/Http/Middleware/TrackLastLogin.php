<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * After every authenticated request, keep last_login_at and last_login_ip fresh.
 * Updates at most once per hour to avoid excessive DB writes.
 */
class TrackLastLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user()) {
            $user    = $request->user();
            $cacheKey = 'last_login_tracked_' . $user->id;

            if (! cache()->has($cacheKey)) {
                $user->timestamps = false;
                $user->forceFill([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                ])->save();
                $user->timestamps = true;

                // Throttle to once per hour
                cache()->put($cacheKey, true, now()->addHour());
            }
        }

        return $response;
    }
}

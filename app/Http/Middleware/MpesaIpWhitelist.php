<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block M-Pesa webhook requests from IPs not in Safaricom's published range.
 *
 * Safaricom Daraja IPs (production):
 *   196.201.214.200/24  — STK Push callbacks
 *   196.201.214.206     — additional Daraja
 *   196.201.214.207     — additional Daraja
 *   196.201.214.196–199 — Daraja range
 *   196.201.214.128/25  — broader Daraja allocation
 *
 * These are applied only on non-local environments.
 */
class MpesaIpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip IP check on local / sandbox environments
        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        $clientIp = $request->server('HTTP_X_FORWARDED_FOR')
            ? explode(',', $request->server('HTTP_X_FORWARDED_FOR'))[0]
            : $request->ip();

        $clientIp = trim($clientIp);

        if (! $this->isAllowed($clientIp)) {
            Log::warning('M-Pesa webhook blocked: unauthorized IP', [
                'ip'   => $clientIp,
                'path' => $request->path(),
            ]);

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Unauthorized'], 403);
        }

        return $next($request);
    }

    private function isAllowed(string $ip): bool
    {
        $allowed = config('mpesa.allowed_ips', [
            // Safaricom Daraja production IPs
            '196.201.214.200',
            '196.201.214.201',
            '196.201.214.202',
            '196.201.214.203',
            '196.201.214.204',
            '196.201.214.205',
            '196.201.214.206',
            '196.201.214.207',
            '196.201.214.208',
            '196.201.214.209',
            '196.201.214.210',
            '196.201.214.211',
            '196.201.214.212',
            '196.201.214.213',
            '196.201.214.214',
            '196.201.214.215',
        ]);

        return in_array($ip, $allowed, true);
    }
}

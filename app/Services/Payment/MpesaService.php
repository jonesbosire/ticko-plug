<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    private string $baseUrl;
    private string $consumerKey;
    private string $consumerSecret;
    private string $shortcode;
    private string $passkey;

    public function __construct()
    {
        $this->baseUrl        = config('mpesa.base_url');
        $this->consumerKey    = config('mpesa.consumer_key');
        $this->consumerSecret = config('mpesa.consumer_secret');
        $this->shortcode      = config('mpesa.shortcode');
        $this->passkey        = config('mpesa.passkey');
    }

    public function getAccessToken(): string
    {
        return Cache::remember('mpesa_access_token', 3300, function () {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");

            if ($response->failed()) {
                Log::error('M-Pesa OAuth failed', ['response' => $response->body()]);
                throw new \RuntimeException('Failed to get M-Pesa access token.');
            }

            return $response->json('access_token');
        });
    }

    public function initiateStkPush(string $phone, float $amount, string $orderNumber, string $description = 'Ticko-Plug Ticket'): array
    {
        $phone     = $this->normalizePhone($phone);
        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => (int) ceil($amount),
            'PartyA'            => $phone,
            'PartyB'            => $this->shortcode,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => config('mpesa.stk_callback_url'),
            'AccountReference'  => $orderNumber,
            'TransactionDesc'   => $description,
        ];

        $response = Http::withToken($this->getAccessToken())
            ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", $payload);

        if ($response->failed() || $response->json('ResponseCode') !== '0') {
            Log::error('STK Push failed', [
                'order'    => $orderNumber,
                'phone'    => $phone,
                'response' => $response->json(),
            ]);
            throw new \RuntimeException($response->json('errorMessage') ?? 'STK Push initiation failed.');
        }

        return $response->json();
    }

    public function queryStkStatus(string $checkoutRequestId): array
    {
        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $response = Http::withToken($this->getAccessToken())
            ->post("{$this->baseUrl}/mpesa/stkpushquery/v1/query", [
                'BusinessShortCode' => $this->shortcode,
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ]);

        return $response->json();
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '254' . substr($digits, 1);
        } elseif (str_starts_with($digits, '+')) {
            $digits = ltrim($digits, '+');
        }

        return $digits;
    }

    public function validateWebhookIp(string $ip): bool
    {
        return in_array($ip, config('mpesa.allowed_ips', []), true);
    }
}

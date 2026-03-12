<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveService
{
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('flutterwave.secret_key');
        $this->baseUrl   = config('flutterwave.base_url');
    }

    public function initiatePayment(array $data): array
    {
        $payload = [
            'tx_ref'       => $data['order_number'],
            'amount'       => $data['amount'],
            'currency'     => 'KES',
            'redirect_url' => config('flutterwave.redirect_url'),
            'customer'     => [
                'email'       => $data['buyer_email'],
                'phonenumber' => $data['buyer_phone'],
                'name'        => $data['buyer_name'],
            ],
            'customizations' => [
                'title'       => 'Ticko-Plug',
                'description' => 'Ticket Purchase - ' . $data['event_title'],
                'logo'        => asset('images/logo.svg'),
            ],
        ];

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/payments", $payload);

        if ($response->failed() || $response->json('status') !== 'success') {
            Log::error('Flutterwave initiation failed', [
                'order'    => $data['order_number'],
                'response' => $response->json(),
            ]);
            throw new \RuntimeException('Card payment initiation failed. Please try again.');
        }

        return $response->json();
    }

    public function verifyTransaction(string $transactionId): array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/transactions/{$transactionId}/verify");

        return $response->json();
    }

    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha256', $payload, config('flutterwave.webhook_secret'));

        return hash_equals($expected, $signature);
    }
}

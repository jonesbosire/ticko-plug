<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $apiKey;
    private string $username;
    private string $senderId;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey   = config('africastalking.api_key');
        $this->username = config('africastalking.username');
        $this->senderId = config('africastalking.sender_id');
        $this->baseUrl  = config('africastalking.base_url');
    }

    public function sendSms(string $phone, string $message): bool
    {
        $phone = $this->normalizePhone($phone);

        try {
            $response = Http::withHeaders([
                'apiKey'       => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept'       => 'application/json',
            ])->asForm()->post($this->baseUrl . config('africastalking.sms_url'), [
                'username' => $this->username,
                'to'       => $phone,
                'message'  => $message,
                'from'     => $this->senderId,
            ]);

            if ($response->failed()) {
                Log::error('SMS sending failed', ['phone' => $phone, 'response' => $response->body()]);

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('SMS exception', ['phone' => $phone, 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function sendWhatsApp(string $phone, string $message): bool
    {
        $phone = $this->normalizePhone($phone);

        try {
            $response = Http::withHeaders([
                'apiKey'       => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post($this->baseUrl . config('africastalking.whatsapp_url'), [
                'username' => $this->username,
                'to'       => $phone,
                'message'  => $message,
                'from'     => config('africastalking.whatsapp_sender'),
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp exception', ['phone' => $phone, 'error' => $e->getMessage()]);

            return false;
        }
    }

    // SMS templates
    public function sendPaymentInitiated(string $phone, string $eventName, string $orderNumber): bool
    {
        $message = "Ticko-Plug: We've received your M-Pesa request for {$eventName}. Check your phone to complete payment. Ref: {$orderNumber}";

        return $this->sendSms($phone, $message);
    }

    public function sendPaymentConfirmed(string $phone, string $buyerName, string $eventName, string $mpesaReceipt): bool
    {
        $message = "Ticko-Plug: Payment confirmed! Hey {$buyerName}, your ticket for {$eventName} is ready. Receipt: {$mpesaReceipt}. Check your email for your ticket. 🎟️";

        return $this->sendSms($phone, $message);
    }

    public function sendEventReminder(string $phone, string $eventName, string $venue, string $time): bool
    {
        $message = "Ticko-Plug: Reminder! {$eventName} is tomorrow at {$time}. Venue: {$venue}. Have your ticket QR ready! 🎉";

        return $this->sendSms($phone, $message);
    }

    public function sendCheckedIn(string $phone, string $eventName): bool
    {
        $message = "Ticko-Plug: ✅ Checked in! Welcome to {$eventName}. Enjoy the experience!";

        return $this->sendSms($phone, $message);
    }

    public function sendEventCancellation(string $phone, string $eventName, string $refundInfo): bool
    {
        $message = "IMPORTANT - Ticko-Plug: {$eventName} has been cancelled. {$refundInfo} Reply HELP for support.";

        return $this->sendSms($phone, $message);
    }

    // WhatsApp templates
    public function sendWhatsAppTicketConfirmation(
        string $phone,
        string $buyerName,
        string $eventName,
        string $date,
        string $venue,
        string $ticketType,
        int $quantity,
        float $amount,
        string $mpesaReceipt
    ): bool {
        $message = "🎟️ *TICKO-PLUG — You're In!*\n\n"
            . "Hey {$buyerName}! Your ticket for *{$eventName}* is confirmed!\n\n"
            . "📅 {$date}\n"
            . "📍 {$venue}\n"
            . "🎫 {$ticketType} × {$quantity}\n"
            . "💳 KES " . number_format($amount) . " paid | Receipt: {$mpesaReceipt}\n\n"
            . "Your ticket QR code has been sent to your email.\n"
            . "Reply *TICKET* to get it here on WhatsApp.\n\n"
            . "🔌 Ticko-Plug";

        return $this->sendWhatsApp($phone, $message);
    }

    public function sendWhatsAppTicket(string $phone, string $ticketNumber, string $eventName, string $attendeeName, string $ticketType): bool
    {
        $message = "Here's your ticket for *{$eventName}* 👇\n\n"
            . "🎫 Ticket: {$ticketNumber}\n"
            . "🏷️ Type: {$ticketType}\n"
            . "👤 Name: {$attendeeName}\n\n"
            . "Show this at the door. Valid once. 🎉\n\n"
            . "🔌 Ticko-Plug";

        return $this->sendWhatsApp($phone, $message);
    }

    public function sendOrganizerSaleNotification(string $phone, string $buyerName, int $quantity, string $ticketType, string $eventName, float $organizerAmount, int $totalSold, int $totalCapacity): bool
    {
        $message = "💰 *New Sale — Ticko-Plug*\n\n"
            . "{$buyerName} bought {$quantity}× {$ticketType} for *{$eventName}*\n"
            . "Amount: KES " . number_format($organizerAmount) . "\n\n"
            . "Total sold: {$totalSold}/{$totalCapacity}";

        return $this->sendWhatsApp($phone, $message);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '254' . substr($digits, 1);
        }

        return '+' . ltrim($digits, '+');
    }
}

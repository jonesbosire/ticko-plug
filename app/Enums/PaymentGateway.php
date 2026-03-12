<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentGateway: string implements HasLabel
{
    case DarajaStk   = 'daraja_stk';
    case DarajaC2B   = 'daraja_c2b';
    case Flutterwave  = 'flutterwave';
    case Pesapal      = 'pesapal';
    case Manual       = 'manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::DarajaStk  => 'M-Pesa (STK Push)',
            self::DarajaC2B  => 'M-Pesa (Paybill/Till)',
            self::Flutterwave => 'Card (Flutterwave)',
            self::Pesapal     => 'Pesapal',
            self::Manual      => 'Manual',
        };
    }
}

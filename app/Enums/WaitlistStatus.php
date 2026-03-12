<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum WaitlistStatus: string implements HasColor, HasLabel
{
    case Waiting   = 'waiting';
    case Notified  = 'notified';
    case Purchased = 'purchased';
    case Expired   = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::Waiting   => 'Waiting',
            self::Notified  => 'Notified',
            self::Purchased => 'Purchased',
            self::Expired   => 'Expired',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Waiting   => 'gray',
            self::Notified  => 'warning',
            self::Purchased => 'success',
            self::Expired   => 'danger',
        };
    }
}

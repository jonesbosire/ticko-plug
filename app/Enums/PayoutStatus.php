<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PayoutStatus: string implements HasColor, HasLabel
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Paid       = 'paid';
    case Failed     = 'failed';
    case OnHold     = 'on_hold';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending    => 'Pending',
            self::Processing => 'Processing',
            self::Paid       => 'Paid',
            self::Failed     => 'Failed',
            self::OnHold     => 'On Hold',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending    => 'gray',
            self::Processing => 'warning',
            self::Paid       => 'success',
            self::Failed     => 'danger',
            self::OnHold     => 'warning',
        };
    }
}

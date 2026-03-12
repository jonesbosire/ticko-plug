<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TicketStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active      = 'active';
    case Used        = 'used';
    case Cancelled   = 'cancelled';
    case Transferred = 'transferred';
    case Refunded    = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active      => 'Active',
            self::Used        => 'Checked In',
            self::Cancelled   => 'Cancelled',
            self::Transferred => 'Transferred',
            self::Refunded    => 'Refunded',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active      => 'success',
            self::Used        => 'info',
            self::Cancelled   => 'danger',
            self::Transferred => 'warning',
            self::Refunded    => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active      => 'heroicon-o-ticket',
            self::Used        => 'heroicon-o-check-badge',
            self::Cancelled   => 'heroicon-o-x-circle',
            self::Transferred => 'heroicon-o-arrows-right-left',
            self::Refunded    => 'heroicon-o-arrow-uturn-left',
        };
    }

    public function isValid(): bool
    {
        return $this === self::Active;
    }
}

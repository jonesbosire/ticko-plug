<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending            = 'pending';
    case Processing         = 'processing';
    case Paid               = 'paid';
    case Failed             = 'failed';
    case Cancelled          = 'cancelled';
    case Refunded           = 'refunded';
    case PartiallyRefunded  = 'partially_refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending           => 'Pending',
            self::Processing        => 'Processing',
            self::Paid              => 'Paid',
            self::Failed            => 'Failed',
            self::Cancelled         => 'Cancelled',
            self::Refunded          => 'Refunded',
            self::PartiallyRefunded => 'Partially Refunded',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending           => 'gray',
            self::Processing        => 'warning',
            self::Paid              => 'success',
            self::Failed            => 'danger',
            self::Cancelled         => 'danger',
            self::Refunded          => 'info',
            self::PartiallyRefunded => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pending           => 'heroicon-o-clock',
            self::Processing        => 'heroicon-o-arrow-path',
            self::Paid              => 'heroicon-o-check-circle',
            self::Failed            => 'heroicon-o-x-circle',
            self::Cancelled         => 'heroicon-o-ban',
            self::Refunded          => 'heroicon-o-arrow-uturn-left',
            self::PartiallyRefunded => 'heroicon-o-arrow-uturn-left',
        };
    }

    public function isComplete(): bool
    {
        return $this === self::Paid;
    }

    public function canBeRefunded(): bool
    {
        return in_array($this, [self::Paid, self::PartiallyRefunded]);
    }
}

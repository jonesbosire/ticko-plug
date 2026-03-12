<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserStatus: string implements HasColor, HasLabel
{
    case Active    = 'active';
    case Suspended = 'suspended';
    case Archived  = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active    => 'Active',
            self::Suspended => 'Suspended',
            self::Archived  => 'Archived',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active    => 'success',
            self::Suspended => 'warning',
            self::Archived  => 'gray',
        };
    }
}

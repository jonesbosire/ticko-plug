<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EventStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft     = 'draft';
    case Published = 'published';
    case Cancelled = 'cancelled';
    case Postponed = 'postponed';
    case Completed = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft     => 'Draft',
            self::Published => 'Published',
            self::Cancelled => 'Cancelled',
            self::Postponed => 'Postponed',
            self::Completed => 'Completed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft     => 'gray',
            self::Published => 'success',
            self::Cancelled => 'danger',
            self::Postponed => 'warning',
            self::Completed => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Draft     => 'heroicon-o-pencil',
            self::Published => 'heroicon-o-check-circle',
            self::Cancelled => 'heroicon-o-x-circle',
            self::Postponed => 'heroicon-o-clock',
            self::Completed => 'heroicon-o-flag',
        };
    }

    public function isPubliclyVisible(): bool
    {
        return in_array($this, [self::Published, self::Completed]);
    }
}

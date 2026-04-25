<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum QrParseStatus: string implements HasColor, HasLabel
{
    case ValidKnownCopy = 'valid_known_copy';
    case ValidUnknownCopy = 'valid_unknown_copy';
    case InvalidFormat = 'invalid_format';
    case Unreadable = 'unreadable';
    case Missing = 'missing';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ValidKnownCopy => 'Valid known copy',
            self::ValidUnknownCopy => 'Valid unknown copy',
            self::InvalidFormat => 'Invalid format',
            self::Unreadable => 'Unreadable',
            self::Missing => 'Missing',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ValidKnownCopy => 'success',
            self::ValidUnknownCopy => 'warning',
            self::InvalidFormat, self::Unreadable, self::Missing => 'danger',
        };
    }
}

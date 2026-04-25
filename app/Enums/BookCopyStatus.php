<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookCopyStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Available = 'available';
    case Processing = 'processing';
    case Lost = 'lost';
    case Archived = 'archived';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Available => 'Available',
            self::Processing => 'Processing',
            self::Lost => 'Lost',
            self::Archived => 'Archived',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Processing => 'warning',
            self::Lost => 'danger',
            self::Archived => 'gray',
            self::Draft => 'info',
        };
    }
}

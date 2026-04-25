<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CaptureSessionStatus: string implements HasColor, HasLabel
{
    case PendingCapture = 'pending_capture';
    case Captured = 'captured';
    case Processing = 'processing';
    case NeedsReview = 'needs_review';
    case Approved = 'approved';
    case Failed = 'failed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PendingCapture => 'Pending capture',
            self::Captured => 'Captured',
            self::Processing => 'Processing',
            self::NeedsReview => 'Needs review',
            self::Approved => 'Approved',
            self::Failed => 'Failed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Approved => 'success',
            self::Failed => 'danger',
            self::NeedsReview => 'warning',
            self::Processing => 'info',
            self::Captured => 'primary',
            self::PendingCapture => 'gray',
        };
    }
}

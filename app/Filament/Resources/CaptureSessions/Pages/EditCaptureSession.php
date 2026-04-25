<?php

namespace App\Filament\Resources\CaptureSessions\Pages;

use App\Filament\Resources\CaptureSessions\CaptureSessionResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCaptureSession extends EditRecord
{
    protected static string $resource = CaptureSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}

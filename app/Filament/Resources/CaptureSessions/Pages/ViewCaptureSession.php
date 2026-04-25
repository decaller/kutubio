<?php

namespace App\Filament\Resources\CaptureSessions\Pages;

use App\Filament\Pages\CaptureBook;
use App\Filament\Resources\CaptureSessions\CaptureSessionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\View\View;

class ViewCaptureSession extends ViewRecord
{
    protected static string $resource = CaptureSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getFooter(): ?View
    {
        if (request()->query('autoback')) {
            return view('filament.pages.capture-session-autoback', [
                'backUrl' => CaptureBook::getUrl(),
            ]);
        }

        return null;
    }
}

<?php

namespace App\Filament\Resources\CaptureSessions\Pages;

use App\Enums\CaptureSessionStatus;
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

    /**
     * Poll the page every 3 seconds while processing.
     */
    public function getHeader(): ?View
    {
        if ($this->record->status === CaptureSessionStatus::Processing) {
            return view('filament.components.processing-poller');
        }

        return null;
    }
}

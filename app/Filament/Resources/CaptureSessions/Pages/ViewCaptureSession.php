<?php

namespace App\Filament\Resources\CaptureSessions\Pages;

use App\Filament\Pages\CaptureBook;
use App\Filament\Resources\CaptureSessions\CaptureSessionResource;
use App\Jobs\ExtractBookDataWithVisionJob;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\View\View;

class ViewCaptureSession extends ViewRecord
{
    protected static string $resource = CaptureSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('extractTitle')
                ->label('Extract Title (AI)')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->action(function () {
                    ExtractBookDataWithVisionJob::dispatch($this->record);

                    Notification::make()
                        ->title('Vision extraction dispatched')
                        ->body('The job has been added to the queue.')
                        ->success()
                        ->send();
                })
                ->disabled(fn () => ! $this->record->front_image_path),
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

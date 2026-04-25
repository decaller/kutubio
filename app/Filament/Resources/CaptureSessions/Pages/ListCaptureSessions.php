<?php

namespace App\Filament\Resources\CaptureSessions\Pages;

use App\Filament\Resources\CaptureSessions\CaptureSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListCaptureSessions extends ListRecords
{
    protected static string $resource = CaptureSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

<?php

namespace App\Filament\Resources\PrintProfiles\Pages;

use App\Filament\Resources\PrintProfiles\PrintProfileResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPrintProfile extends ViewRecord
{
    protected static string $resource = PrintProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\PrintProfiles\Pages;

use App\Filament\Resources\PrintProfiles\PrintProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrintProfiles extends ListRecords
{
    protected static string $resource = PrintProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

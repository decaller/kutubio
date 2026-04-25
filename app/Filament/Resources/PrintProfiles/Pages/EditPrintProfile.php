<?php

namespace App\Filament\Resources\PrintProfiles\Pages;

use App\Filament\Resources\PrintProfiles\PrintProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPrintProfile extends EditRecord
{
    protected static string $resource = PrintProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

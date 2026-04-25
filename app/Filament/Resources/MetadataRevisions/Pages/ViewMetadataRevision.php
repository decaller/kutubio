<?php

namespace App\Filament\Resources\MetadataRevisions\Pages;

use App\Filament\Resources\MetadataRevisions\MetadataRevisionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMetadataRevision extends ViewRecord
{
    protected static string $resource = MetadataRevisionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

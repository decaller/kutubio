<?php

namespace App\Filament\Resources\QueueJobs\Pages;

use App\Filament\Resources\QueueJobs\QueueJobResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageQueueJobs extends ManageRecords
{
    protected static string $resource = QueueJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}

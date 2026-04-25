<?php

namespace App\Filament\Resources\Jobs\Pages;

use App\Filament\Resources\Jobs\JobResource;
use App\Models\FailedJob;
use App\Models\QueueJob;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ManageJobs extends ManageRecords
{
    protected static string $resource = JobResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Active Jobs')
                ->icon('heroicon-m-play-circle')
                ->badge(QueueJob::count()),
            'failed' => Tab::make('Failed Jobs')
                ->icon('heroicon-m-exclamation-triangle')
                ->badge(FailedJob::count())
                ->badgeColor('danger'),
        ];
    }
}

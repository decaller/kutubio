<?php

namespace App\Filament\Resources\Jobs\Pages;

use App\Filament\Resources\Jobs\JobResource;
use App\Models\FailedJob;
use App\Models\QueueJob;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class ListFailedJobs extends ManageRecords
{
    protected static string $resource = JobResource::class;

    protected static ?string $title = 'Failed Jobs';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

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

    public function table(Table $table): Table
    {
        return $table
            ->query(FailedJob::query())
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('queue')
                    ->badge()
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label('Job Name')
                    ->searchable(),
                TextColumn::make('exception')
                    ->label('Failure Reason')
                    ->limit(100)
                    ->wrap()
                    ->prose()
                    ->tooltip(fn ($state) => $state),
                TextColumn::make('failed_at')
                    ->dateTime()
                    ->sortable()
                    ->color('danger'),
            ])
            ->defaultSort('failed_at', 'desc')
            ->recordActions([
                Action::make('retry')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->action(function ($record) {
                        Artisan::call('queue:retry', [
                            'id' => [$record->uuid],
                        ]);

                        Notification::make()
                            ->title('Job added back to queue')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ]);
    }
}

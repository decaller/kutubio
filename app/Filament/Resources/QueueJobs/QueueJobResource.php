<?php

namespace App\Filament\Resources\QueueJobs;

use App\Filament\Resources\QueueJobs\Pages;
use App\Models\QueueJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use UnitEnum;

class QueueJobResource extends Resource
{
    protected static ?string $model = QueueJob::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('queue')
                    ->badge()
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label('Job')
                    ->searchable(),
                TextColumn::make('attempts')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageQueueJobs::route('/'),
        ];
    }
}

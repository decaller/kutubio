<?php

namespace App\Filament\Resources\MetadataRevisions\Tables;

use App\Enums\MetadataRevisionType;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MetadataRevisionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('revision_type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('book.title')
                    ->searchable()
                    ->placeholder('No book yet')
                    ->wrap(),
                TextColumn::make('captureSession.public_id')
                    ->label('Capture session')
                    ->searchable()
                    ->copyable()
                    ->placeholder('None'),
                TextColumn::make('source_stage')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('confidence_score')
                    ->numeric(decimalPlaces: 4)
                    ->sortable()
                    ->placeholder('None'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('revision_type')
                    ->options(MetadataRevisionType::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\PrintProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrintProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('page_width_mm')
                    ->suffix(' mm')
                    ->sortable(),
                TextColumn::make('page_height_mm')
                    ->suffix(' mm')
                    ->sortable(),
                TextColumn::make('grid_columns')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('grid_rows')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('slot_width_mm')
                    ->suffix(' mm')
                    ->toggleable(),
                TextColumn::make('slot_height_mm')
                    ->suffix(' mm')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

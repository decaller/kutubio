<?php

namespace App\Filament\Resources\MetadataRevisions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MetadataRevisionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Revision')
                    ->schema([
                        TextEntry::make('book.title')
                            ->placeholder('No book yet'),
                        TextEntry::make('captureSession.public_id')
                            ->label('Capture session')
                            ->placeholder('None')
                            ->copyable(),
                        TextEntry::make('revision_type')
                            ->badge(),
                        TextEntry::make('source_stage'),
                        TextEntry::make('source_actor_type')
                            ->placeholder('None'),
                        TextEntry::make('source_actor_id')
                            ->placeholder('None'),
                        TextEntry::make('confidence_score')
                            ->numeric(decimalPlaces: 4)
                            ->placeholder('None'),
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('payload')
                            ->formatStateUsing(fn (mixed $state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}')
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                        TextEntry::make('diff_from_previous')
                            ->formatStateUsing(fn (mixed $state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}')
                            ->fontFamily('mono')
                            ->placeholder('None')
                            ->columnSpanFull(),
                        TextEntry::make('source_meta')
                            ->formatStateUsing(fn (mixed $state): string => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}')
                            ->fontFamily('mono')
                            ->placeholder('None')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}

<?php

namespace App\Filament\Resources\MetadataRevisions\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MetadataRevisionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Revision')
                    ->schema([
                        Select::make('book_id')
                            ->relationship('book', 'title')
                            ->searchable()
                            ->preload()
                            ->disabled(),
                        Select::make('capture_session_id')
                            ->relationship('captureSession', 'public_id')
                            ->searchable()
                            ->preload()
                            ->disabled(),
                        TextInput::make('revision_type')
                            ->disabled(),
                        TextInput::make('source_stage')
                            ->disabled(),
                        TextInput::make('confidence_score')
                            ->disabled(),
                        KeyValue::make('payload')
                            ->disabled()
                            ->columnSpanFull(),
                        KeyValue::make('diff_from_previous')
                            ->disabled()
                            ->columnSpanFull(),
                        KeyValue::make('source_meta')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}

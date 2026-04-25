<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category')
                    ->schema([
                        TextEntry::make('code')
                            ->copyable(),
                        TextEntry::make('label'),
                        TextEntry::make('short_label')
                            ->placeholder('None'),
                        TextEntry::make('color')
                            ->placeholder('None'),
                        TextEntry::make('sort_order')
                            ->numeric()
                            ->placeholder('None'),
                        TextEntry::make('source_version')
                            ->placeholder('None'),
                        TextEntry::make('books_count')
                            ->label('Books')
                            ->numeric(),
                    ])
                    ->columns(2),
            ]);
    }
}

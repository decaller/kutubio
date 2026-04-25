<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('DDC category')
                    ->schema([
                        TextInput::make('code')
                            ->required()
                            ->maxLength(16)
                            ->unique(ignoreRecord: true),
                        TextInput::make('label')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('short_label')
                            ->maxLength(255),
                        ColorPicker::make('color'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('source_version')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}

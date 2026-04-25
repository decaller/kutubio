<?php

namespace App\Filament\Resources\BookCopies;

use App\Filament\Resources\BookCopies\Pages\CreateBookCopy;
use App\Filament\Resources\BookCopies\Pages\EditBookCopy;
use App\Filament\Resources\BookCopies\Pages\ListBookCopies;
use App\Filament\Resources\BookCopies\Pages\ViewBookCopy;
use App\Filament\Resources\BookCopies\Schemas\BookCopyForm;
use App\Filament\Resources\BookCopies\Schemas\BookCopyInfolist;
use App\Filament\Resources\BookCopies\Tables\BookCopiesTable;
use App\Models\BookCopy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BookCopyResource extends Resource
{
    protected static ?string $model = BookCopy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmarkSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Library';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'public_id';

    public static function form(Schema $schema): Schema
    {
        return BookCopyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BookCopyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookCopiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookCopies::route('/'),
            'create' => CreateBookCopy::route('/create'),
            'view' => ViewBookCopy::route('/{record}'),
            'edit' => EditBookCopy::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('book');
    }
}

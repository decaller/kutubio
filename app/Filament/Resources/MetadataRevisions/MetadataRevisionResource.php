<?php

namespace App\Filament\Resources\MetadataRevisions;

use App\Filament\Resources\MetadataRevisions\Pages\ListMetadataRevisions;
use App\Filament\Resources\MetadataRevisions\Pages\ViewMetadataRevision;
use App\Filament\Resources\MetadataRevisions\Schemas\MetadataRevisionForm;
use App\Filament\Resources\MetadataRevisions\Schemas\MetadataRevisionInfolist;
use App\Filament\Resources\MetadataRevisions\Tables\MetadataRevisionsTable;
use App\Models\MetadataRevision;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MetadataRevisionResource extends Resource
{
    protected static ?string $model = MetadataRevision::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Intake';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return MetadataRevisionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MetadataRevisionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MetadataRevisionsTable::configure($table);
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
            'index' => ListMetadataRevisions::route('/'),
            'view' => ViewMetadataRevision::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['book', 'captureSession']);
    }
}

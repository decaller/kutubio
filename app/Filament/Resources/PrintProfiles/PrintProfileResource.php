<?php

namespace App\Filament\Resources\PrintProfiles;

use App\Filament\Resources\PrintProfiles\Pages\CreatePrintProfile;
use App\Filament\Resources\PrintProfiles\Pages\EditPrintProfile;
use App\Filament\Resources\PrintProfiles\Pages\ListPrintProfiles;
use App\Filament\Resources\PrintProfiles\Pages\ViewPrintProfile;
use App\Filament\Resources\PrintProfiles\Schemas\PrintProfileForm;
use App\Filament\Resources\PrintProfiles\Schemas\PrintProfileInfolist;
use App\Filament\Resources\PrintProfiles\Tables\PrintProfilesTable;
use App\Models\PrintProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PrintProfileResource extends Resource
{
    protected static ?string $model = PrintProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPrinter;

    protected static string|UnitEnum|null $navigationGroup = 'Printing';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PrintProfileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PrintProfileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrintProfilesTable::configure($table);
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
            'index' => ListPrintProfiles::route('/'),
            'create' => CreatePrintProfile::route('/create'),
            'view' => ViewPrintProfile::route('/{record}'),
            'edit' => EditPrintProfile::route('/{record}/edit'),
        ];
    }
}

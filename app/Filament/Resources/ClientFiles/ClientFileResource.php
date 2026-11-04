<?php

namespace App\Filament\Resources\ClientFiles;

use App\Filament\Resources\ClientFiles\Pages\CreateClientFile;
use App\Filament\Resources\ClientFiles\Pages\EditClientFile;
use App\Filament\Resources\ClientFiles\Pages\ListClientFiles;
use App\Filament\Resources\ClientFiles\Schemas\ClientFileForm;
use App\Filament\Resources\ClientFiles\Tables\ClientFilesTable;
use App\Models\ClientFile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClientFileResource extends Resource
{
    protected static ?string $model = ClientFile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ClientFileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientFilesTable::configure($table);
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
            'index' => ListClientFiles::route('/'),
            'create' => CreateClientFile::route('/create'),
            'edit' => EditClientFile::route('/{record}/edit'),
        ];
    }
}

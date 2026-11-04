<?php

namespace App\Filament\Resources\ClientOrganizations;

use App\Filament\Resources\ClientOrganizations\Pages\CreateClientOrganization;
use App\Filament\Resources\ClientOrganizations\Pages\EditClientOrganization;
use App\Filament\Resources\ClientOrganizations\Pages\ListClientOrganizations;
use App\Filament\Resources\ClientOrganizations\Schemas\ClientOrganizationForm;
use App\Filament\Resources\ClientOrganizations\Tables\ClientOrganizationsTable;
use App\Models\ClientOrganization;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClientOrganizationResource extends Resource
{
    protected static ?string $model = ClientOrganization::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ClientOrganizationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientOrganizationsTable::configure($table);
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
            'index' => ListClientOrganizations::route('/'),
            'create' => CreateClientOrganization::route('/create'),
            'edit' => EditClientOrganization::route('/{record}/edit'),
        ];
    }
}

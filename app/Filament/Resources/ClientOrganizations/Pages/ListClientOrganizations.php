<?php

namespace App\Filament\Resources\ClientOrganizations\Pages;

use App\Filament\Resources\ClientOrganizations\ClientOrganizationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientOrganizations extends ListRecords
{
    protected static string $resource = ClientOrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

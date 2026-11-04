<?php

namespace App\Filament\Resources\ClientOrganizations\Pages;

use App\Filament\Resources\ClientOrganizations\ClientOrganizationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientOrganization extends EditRecord
{
    protected static string $resource = ClientOrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

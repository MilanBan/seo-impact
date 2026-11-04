<?php

namespace App\Filament\Resources\ClientFiles\Pages;

use App\Filament\Resources\ClientFiles\ClientFileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientFiles extends ListRecords
{
    protected static string $resource = ClientFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ClientFiles\Pages;

use App\Filament\Resources\ClientFiles\ClientFileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientFile extends EditRecord
{
    protected static string $resource = ClientFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

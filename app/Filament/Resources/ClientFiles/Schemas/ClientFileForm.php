<?php

namespace App\Filament\Resources\ClientFiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ClientFileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'name')
                    ->required(),
                TextInput::make('uploaded_by')
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('path')
                    ->required(),
                TextInput::make('disk')
                    ->required()
                    ->default('local'),
                TextInput::make('mime_type')
                    ->required(),
                TextInput::make('size')
                    ->required()
                    ->numeric(),
                Toggle::make('client_visible')
                    ->required(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required(),
                Repeater::make('translations')->relationship()->schema([
                    TextInput::make('locale')->required()->maxLength(5),
                    TextInput::make('name')->required(),
                    TextInput::make('slug')->required(),
                ])->columns(3)->columnSpanFull(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required(),
                Repeater::make('translations')->relationship()->schema([
                    TextInput::make('locale')->required()->maxLength(5),
                    TextInput::make('title')->required(),
                    TextInput::make('headline')->required(),
                    TextInput::make('slug')->required(),
                    Textarea::make('summary')->columnSpanFull(),
                    TextInput::make('seo_title'),
                    Textarea::make('seo_description'),
                    Toggle::make('published')->default(false),
                ])->columns(2)->columnSpanFull()->minItems(1),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required(),
                TextInput::make('icon')
                    ->required()
                    ->default('search'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('active')
                    ->required(),
                Repeater::make('translations')->relationship()->schema([
                    TextInput::make('locale')->required()->maxLength(5),
                    TextInput::make('title')->required(),
                    TextInput::make('slug')->required(),
                    Textarea::make('summary')->required()->columnSpanFull(),
                    RichEditor::make('body')->columnSpanFull(),
                    TextInput::make('seo_title'),
                    Textarea::make('seo_description'),
                    Toggle::make('published')->default(false),
                ])->columns(2)->columnSpanFull()->minItems(1),
            ]);
    }
}

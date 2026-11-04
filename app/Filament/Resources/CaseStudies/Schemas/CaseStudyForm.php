<?php

namespace App\Filament\Resources\CaseStudies\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CaseStudyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('client_name')
                    ->required(),
                TextInput::make('industry'),
                FileUpload::make('cover_image')
                    ->image(),
                Repeater::make('metrics')->schema([
                    TextInput::make('value')->required(),
                    TextInput::make('label')->required(),
                ])->columns(2),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
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

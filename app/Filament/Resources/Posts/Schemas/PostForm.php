<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'id'),
                Select::make('author_id')
                    ->relationship('author', 'name'),
                FileUpload::make('cover_image')
                    ->image(),
                DateTimePicker::make('published_at'),
                Repeater::make('translations')->relationship()->schema([
                    TextInput::make('locale')->required()->maxLength(5),
                    TextInput::make('title')->required(),
                    TextInput::make('slug')->required(),
                    Textarea::make('excerpt')->required()->columnSpanFull(),
                    RichEditor::make('body')->required()->columnSpanFull(),
                    TextInput::make('seo_title'),
                    Textarea::make('seo_description'),
                    Toggle::make('published')->default(false),
                ])->columns(2)->columnSpanFull()->minItems(1),
            ]);
    }
}

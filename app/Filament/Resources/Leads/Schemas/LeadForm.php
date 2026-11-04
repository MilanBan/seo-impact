<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('company'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('service'),
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('locale')
                    ->required()
                    ->default('en'),
                TextInput::make('source')
                    ->required()
                    ->default('contact'),
                TextInput::make('status')
                    ->required()
                    ->default('new'),
                TextInput::make('utm_source'),
                TextInput::make('utm_medium'),
                TextInput::make('utm_campaign'),
                Textarea::make('internal_note')
                    ->columnSpanFull(),
            ]);
    }
}

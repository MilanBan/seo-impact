<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('financial_record_id')
                    ->relationship('financialRecord', 'document_number')->searchable()->preload()
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()->minValue(0.01),
                DatePicker::make('paid_at')
                    ->required(),
                TextInput::make('reference'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}

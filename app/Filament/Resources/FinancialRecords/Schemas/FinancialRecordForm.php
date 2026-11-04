<?php

namespace App\Filament\Resources\FinancialRecords\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FinancialRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'name')
                    ->required(),
                TextInput::make('document_number')
                    ->required(),
                Select::make('type')->options(['invoice' => 'Invoice', 'quote' => 'Quote', 'credit_note' => 'Credit note'])->required()->default('invoice'),
                Select::make('currency')->options(['EUR' => 'EUR', 'RSD' => 'RSD', 'USD' => 'USD', 'GBP' => 'GBP'])->required()->default('EUR'),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                DatePicker::make('issued_at')
                    ->required(),
                DatePicker::make('due_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}

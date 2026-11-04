<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_organization_id')
                    ->relationship('organization', 'name')->searchable()->preload()->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('status')->options(['planned' => 'Planned', 'active' => 'Active', 'paused' => 'Paused', 'completed' => 'Completed'])->required()->default('planned'),
                DatePicker::make('starts_at'),
                DatePicker::make('due_at'),
            ]);
    }
}

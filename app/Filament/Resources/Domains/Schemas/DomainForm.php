<?php

namespace App\Filament\Resources\Domains\Schemas;

use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;

class DomainForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->options(function () {
                        return User::pluck('name', 'id');
                }),
                TextInput::make('nama_domain')
                    ->required(),
                TextInput::make('ekstensi')
                    ->required(),
                TextInput::make('ns1'),
                TextInput::make('ns2'),
                TextInput::make('ns3'),
                TextInput::make('ns4'),
                TextInput::make('ip'),
                DatePicker::make('expired'),
            ]);
    }
}

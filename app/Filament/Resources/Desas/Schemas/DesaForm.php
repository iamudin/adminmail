<?php

namespace App\Filament\Resources\Desas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DesaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kecamatan'),
                TextInput::make('nama')
            ]);
    }
}

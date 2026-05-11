<?php

namespace App\Filament\Resources\Desas\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;

class DesasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('row_number')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('kecamatan.nama_kecamatan')
                    ->searchable()->sortable()
                    ->label('Nama Kecamatan'),
                TextColumn::make('type')->sortable()
                    ->label('Jenis')
                    ->badge(),
                TextColumn::make('nama')
                    ->searchable()->sortable()
                    ->label('Nama Kel/Desa'),
                TextColumn::make('domain.nama_domain')
                    ->state(fn($record)=> $record->domain()->exists() ? $record->domain->nama_domain : '-')
                    ->searchable()
                    ->label('Nama Domain'),
              
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->fillForm(fn($record) => [
                        'nama_kecamatan' => $record['kecamatan']['nama_kecamatan'],
                        'nama' => $record['type'].' '.$record['nama'],
                       
                    ]),
            ])
            ->toolbarActions([
            
            ]);
    }
}

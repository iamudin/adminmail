<?php

namespace App\Filament\Resources\Domains\RelationManagers;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\Domains\DomainResource;
use Filament\Resources\RelationManagers\RelationManager;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_number')
                    ->label('Invoice Number')
                    ->required()
                    ->default(fn() => $this->generateInvoiceNumber())   // untuk create
                    ->afterStateHydrated(function ($state, $set, $record) {
                        if (!$record) {
                            $set('invoice_number', $this->generateInvoiceNumber());
                        }
                    })
                    ->disabled() // jika tidak mau di-edit user
                    ->dehydrated(true), // tetap masuk database
                TextInput::make('jumlah_tagihan')
                    ->numeric()
                    ->prefix('Rp')
     ->dehydrateStateUsing(fn($state) => is_null($state) ? null : preg_replace('/\D/', '', (string) $state))
    ->rules(['required', 'regex:/^\d+$/']),
                DatePicker::make('jatuh_tempo')
                    ->required()
            ]);
    }
    public function generateInvoiceNumber()
    {
        $last = \App\Models\Invoice::orderBy('id', 'desc')->first();

        $number = $last ? $last->id + 1 : 1;

        return 'INV-' . date('Ymd').str_pad($number, 3, '0', STR_PAD_LEFT);
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Invoices')
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Nomor')
                    ->searchable(),
                TextColumn::make(name: 'jumlah_tagihan')
                    ->label('Jumlah Tagihan')
                    ->label('Harga')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

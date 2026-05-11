<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use App\Services\CloudflareService;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Filament\Tables\Concerns\InteractsWithTable;

class DnsManager extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected string $view = 'filament.pages.dns-manager';
    protected ?CloudflareService $service = null;

    public array $apiData = [];
    protected $listeners = ['refresh' => 'refresh'];
    public function mount(): void
    {
        $this->service = app(CloudflareService::class);
    }
    public static function canAccess(): bool
    {
        return false;
    }
    public static function formSchema(): array
    {
        return [
            Select::make('type')
                ->options([
                    'A' => 'A',
                    'AAAA' => 'AAAA',
                    'CNAME' => 'CNAME',
                    'MX' => 'MX',
                    'TXT' => 'TXT',
                    'SRV' => 'SRV',
                    'NS' => 'NS',
                ])
                ->required(),
            TextInput::make('name')->required()->placeholder('subdomain.example.com'),
            TextInput::make('content')->required()->placeholder('123.123.123.123'),
            TextInput::make('ttl')->numeric()->default(3600)->label('TTL (detik)'),
            Toggle::make('proxied')->label('Gunakan Cloudflare Proxy')->default(true),
        ];
    }


    protected function getService(): CloudflareService
    {
        return $this->service ??= app(CloudflareService::class);
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('type')->label('Type'),
                TextColumn::make('name')->label('Name')->searchable(),
                TextColumn::make('content')->label('Content')->searchable(),
                IconColumn::make('proxied')->boolean()->label('Proxy'),
                TextColumn::make('ttl')->label('TTL'),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->form(self::formSchema())
                    ->fillForm(fn($record) => [
                        'type' => $record['type'],
                        'name' => $record['name'],
                        'content' => $record['content'],
                        'ttl' => $record['ttl'],
                        'proxied' => $record['proxied'],
                    ])
                    ->action(function ($record, array $data) {
                        $this->getService()->updateRecord($record['id'], $data);
                        Notification::make()->title('Record berhasil diperbarui')->success()->send();
                        $this->dispatch('refresh');
                    }),

                Action::make('delete')
                    ->label('Delete')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->action(function ($record) {
                        $this->getService()->deleteRecord($record['id']);
                        Notification::make()->title('Record berhasil dihapus')->success()->send();
                        $this->dispatch('refresh');
                    }),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Tambah Record')
                    ->icon('heroicon-o-plus')
                    ->form(fn() => self::formSchema())
                    ->action(function (array $data) {
                        $this->getService()->createRecord($data);
                        Notification::make()->title('Record berhasil dibuat')->success()->send();
                        $this->dispatch('$refresh');
                    }),
            ])
            ->records(function () {
                $page = $this->getTablePage();
                $perPage = 10;
                $search = $this->getTableSearch() ?? '';

                // kirim parameter pencarian langsung ke API
                $data = $this->getService()->listRecords($page, $perPage, $search);
                $records = collect($data['result'] ?? []);

                $info = $data['result_info'] ?? [
                    'page' => 1,
                    'per_page' => $perPage,
                    'total_count' => $records->count(),
                ];

                return new \Illuminate\Pagination\LengthAwarePaginator(
                    $records,
                    $info['total_count'],
                    $info['per_page'],
                    $info['page'],
                    ['path' => request()->url(), 'query' => request()->query()]
                );
            })


            ->paginated();
    }

    #[\Livewire\Attributes\On('table::updatedTablePage')]
    public function reloadTablePage(string $table, int $page): void
    {
        if ($table === $this->getTableName()) {
            $this->dispatch('$refresh');
        }
    }
}

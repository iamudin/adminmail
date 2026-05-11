<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms;
use Filament\Tables;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Services\CpanelApiService;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class EmailManager extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    protected  string $view = 'filament.pages.email-manager';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static function getPasswordForm(): array
    {
        return [
            Forms\Components\TextInput::make('password')
                ->password()
                ->required()
                ->revealable()
                ->live()
                ->rule(Password::min(12)->letters()->mixedCase()->numbers()->symbols())
                ->suffixAction(
                    Action::make('generatePassword')
                        ->icon('heroicon-m-key')
                        ->tooltip('Generate Password')
                        ->action(function ($set) {
                            $password = Str::random(16) . '!@#$';
                            $password = str_shuffle($password);
                            $set('password', $password);
                        })
                ),
        ];
    }

    public function table(Table $table): Table
    {
      

        return $table->deferLoading()
            ->records(fn()=>$this->getPaginatedEmails())
            ->columns([
                Tables\Columns\TextColumn::make('email')->searchable(),
               
            ]) ->paginated(true)
            ->headerActions([
                Action::make('create')
                    ->label('Tambah Email')
                    ->form([
                        Forms\Components\TextInput::make('email')->required(),
                        ...self::getPasswordForm(),
                        Forms\Components\TextInput::make('quota')->numeric()->default(250),
                    ])
                    ->action(fn ($data) => (new CpanelApiService())->createEmail(
                        $data['email'], $data['password'], $data['quota']
                    )),
            ])
            ->actions([
              Action::make('changePassword')
                    ->form(self::getPasswordForm())
                    ->action(fn ($data, $record) => (new CpanelApiService())->changePassword(
                        $record['email'], $data['password']
                    )),
                Action::make('delete')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn ($record) => (new CpanelApiService())->deleteEmail($record['email'])),
            ]);
    }

    protected function getPaginatedEmails(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $api = new CpanelApiService();

        // Ambil data dari API
        $emails = collect($api->listEmails())->map(fn($item) => [
            'email' => $item['email']
        ]);

        // 🔍 Filter manual berdasarkan input search
        $search = trim(strtolower($this->tableSearch ?? ''));
        if ($search !== '') {
            $emails = $emails->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['email']), $search)
                    || str_contains(strtolower($item['email']), $search);
            });
        }

        // 🔢 Pagination manual
        $page = Paginator::resolveCurrentPage('page');
        $perPage = 10;
        $items = $emails->forPage($page, $perPage);

        return new LengthAwarePaginator(
            $items->values(),
            $emails->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );
    }
}

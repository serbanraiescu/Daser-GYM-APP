<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;

class ManageSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|\UnitEnum|null $navigationGroup = 'Configurare';
    protected static ?string $title = 'Setări Sistem';
    protected static ?string $navigationLabel = 'Setări';
    
    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(
            Setting::all()->pluck('value', 'key')->toArray()
        );
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Imagine Brand')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                TextInput::make('gym_name')
                                    ->label('Nume Sală')
                                    ->required(),
                                FileUpload::make('gym_logo')
                                    ->label('Logotip (Logo)')
                                    ->image()
                                    ->directory('branding'),
                                ColorPicker::make('gym_primary_color')
                                    ->label('Culoare Principală'),
                            ]),
                        Tab::make('Reguli Afacere')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                TextInput::make('currency')
                                    ->label('Monedă (ex: RON)'),
                                TextInput::make('grace_days')
                                    ->label('Zile de Grație (după expirare)')
                                    ->numeric(),
                                Select::make('timezone')
                                    ->label('Fus Orar')
                                    ->options([
                                        'Europe/Bucharest' => 'Europa/București',
                                        'UTC' => 'UTC',
                                    ]),
                            ]),
                        Tab::make('Șabloane Mesaje')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                TextInput::make('email_welcome_subject')
                                    ->label('Subiect Email Bun Venit'),
                                // Textarea::make('sms_welcome_template') will be moved to SMS tab
                            ]),
                        Tab::make('Configurare SMS')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                TextInput::make('sms_gateway_link')
                                    ->label('Link Conectare Android')
                                    ->helperText('Copiați acest link în aplicația Android pentru a conecta telefonul.')
                                    ->readOnly(),
                                Textarea::make('sms_gateway_status')
                                    ->label('Jurnal Activitate / Log Dispozitiv')
                                    ->rows(5)
                                    ->readOnly(),
                                Textarea::make('sms_welcome_template')
                                    ->label('Șablon SMS Bun Venit')
                                    ->helperText('Mesajul trimis automat noilor membri.')
                                    ->rows(3),
                            ]),

                        Tab::make('Pagini Legale')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                \Filament\Forms\Components\RichEditor::make('legal_terms')
                                    ->label('Termeni și Condiții'),
                                \Filament\Forms\Components\RichEditor::make('legal_privacy')
                                    ->label('Politica de Confidențialitate'),
                            ]),

                        Tab::make('Licențiere')
                            ->icon('heroicon-o-key')
                            ->schema([
                                TextInput::make('license_key')
                                    ->label('Cheie Licență')
                                    ->password()
                                    ->placeholder('LIC-XXXX-XXXX')
                                    ->helperText('Cheia primită de la Daser Design.'),
                                
                                \Filament\Forms\Components\Placeholder::make('license_status_display')
                                    ->label('Status Licență')
                                    ->content(function ($get) {
                                        $cache = json_decode(Setting::where('key', 'license_status_cache')->first()?->value ?? '{}', true);
                                        $status = $cache['status'] ?? 'unknown';
                                        $days = $cache['days_left'] ?? 0;
                                        
                                        $color = match($status) {
                                            'active' => 'text-success-600',
                                            'denied' => 'text-danger-600',
                                            default => 'text-gray-600',
                                        };

                                        return new \Illuminate\Support\HtmlString("<span class='font-bold {$color}'>" . strtoupper($status) . "</span>" . ($days ? " ({$days} zile rămase)" : ""));
                                    }),

                                \Filament\Forms\Components\Placeholder::make('license_last_check_display')
                                    ->label('Ultima Verificare')
                                    ->content(fn () => Setting::where('key', 'license_last_check')->first()?->value ?? 'Niciodată'),

                                TextInput::make('license_kill_token')
                                    ->label('Kill Token (Secret)')
                                    ->password()
                                    ->readOnly()
                                    ->helperText('Folosiți acest token în Master App pentru Revocare Imediată.')
                                    ->copyable(),
                            ]),
                    ])
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('verify_license')
                ->label('Verifică Licența')
                ->color('info')
                ->icon('heroicon-o-arrow-path')
                ->action('verifyLicense'),
            Action::make('save')
                ->label('Salvează Setările')
                ->submit('save'),
        ];
    }

    public function verifyLicense(): void
    {
        try {
            $licenseService = app(\App\Services\LicenseService::class);
            $cache = $licenseService->checkLicense(true);

            $this->mount(); // Refresh form data

            Notification::make()
                ->title('Licență sincronizată!')
                ->body('Status: ' . strtoupper($cache['status'] ?? 'unknown'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Eroare sincronizare')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            foreach ($data as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value ?? '']
                );
                
                // Bust individual cache for each setting
                Cache::forget('setting:' . $key);
            }

            Cache::forget('public_settings');
            Cache::forget('settings:public');
            Cache::forget('public:website');

            Notification::make()
                ->title('Setările au fost salvate!')
                ->success()
                ->send();
        } catch (\Exception $exception) {
            Notification::make()
                ->title('Eroare la salvare: ' . $exception->getMessage())
                ->danger()
                ->send();
        }
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\ColorPicker;
use Filament\Schemas\Components\Textarea;
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
                        Tabs\Tab::make('Imagine Brand')
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
                        Tabs\Tab::make('Reguli Afacere')
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
                        Tabs\Tab::make('Șabloane Mesaje')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                TextInput::make('email_welcome_subject')
                                    ->label('Subiect Email Bun Venit'),
                                // Textarea::make('sms_welcome_template') will be moved to SMS tab
                            ]),
                        Tabs\Tab::make('Configurare SMS')
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

                        Tabs\Tab::make('Pagini Legale')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                \Filament\Schemas\Components\RichEditor::make('legal_terms')
                                    ->label('Termeni și Condiții'),
                                \Filament\Schemas\Components\RichEditor::make('legal_privacy')
                                    ->label('Politica de Confidențialitate'),
                            ]),
                    ])
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvează Setările')
                ->submit('save'),
        ];
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

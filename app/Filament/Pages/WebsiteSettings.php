<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\ColorPicker;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Components\Select;
use Illuminate\Support\Arr;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;

class WebsiteSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string|\UnitEnum|null $navigationGroup = 'Website (Public)';
    protected static ?string $title = 'Website Builder';
    protected static ?string $navigationLabel = 'Design Site';
    
    protected string $view = 'filament.pages.website-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::where('is_public', true)
            ->where(function ($q) {
                $q->where('key', 'like', 'website.%')
                  ->orWhere('key', 'like', 'gym_%') // Fallbacks
                  ->orWhere('key', 'like', 'social_%');
            })
            ->pluck('value', 'key');

        $schemaatJson = function ($key) use ($settings) {
            $val = $settings->get($key);
            return $val ? json_decode($val, true) : [];
        };

        $schemaatBool = function ($key, $default = true) use ($settings) {
            return $settings->has($key) ? filter_var($settings->get($key), FILTER_VALIDATE_BOOLEAN) : $default;
        };

        $fillData = [
            // Branding
            'website.brand.name' => $settings->get('website.brand.name', $settings->get('gym_name')),
            'website.brand.logo_url' => $settings->get('website.brand.logo_url', $settings->get('gym_logo')),
            'website.brand.favicon_url' => $settings->get('website.brand.favicon_url'),
            'website.theme.primary_color' => $settings->get('website.theme.primary_color', $settings->get('gym_primary_color')),
            'website.theme.secondary_color' => $settings->get('website.theme.secondary_color', '#1e293b'),
            // Header
            'website.header.nav.items' => $schemaatJson('website.header.nav.items'),
            'website.header.cta.primary.label' => $settings->get('website.header.cta.primary.label', 'Autentificare'),
            'website.header.cta.primary.href' => $settings->get('website.header.cta.primary.href', '/app/login'),
            // Hero
            'website.hero.enabled' => $schemaatBool('website.hero.enabled'),
            'website.hero.title' => $settings->get('website.hero.title', 'Transformă-ți Corpul Astăzi.'),
            'website.hero.subtitle' => $settings->get('website.hero.subtitle', $settings->get('gym_description')),
            'website.hero.image_url' => $settings->get('website.hero.image_url'),
            'website.hero.primary_button.label' => $settings->get('website.hero.primary_button.label', 'Abonamente'),
            'website.hero.primary_button.href' => $settings->get('website.hero.primary_button.href', '#abonamente'),
            'website.hero.secondary_button.label' => $settings->get('website.hero.secondary_button.label', 'Contact'),
            'website.hero.secondary_button.href' => $settings->get('website.hero.secondary_button.href', '#contact'),
            // Features
            'website.features.enabled' => $schemaatBool('website.features.enabled'),
            'website.features.title' => $settings->get('website.features.title', 'De ce noi?'),
            'website.features.items' => $schemaatJson('website.features.items'),
            // Plans
            'website.plans.enabled' => $schemaatBool('website.plans.enabled'),
            'website.plans.title' => $settings->get('website.plans.title', 'Planuri'),
            'website.plans.subtitle' => $settings->get('website.plans.subtitle', 'Alege ce ți se potrivește.'),
            'website.plans.show_prices' => $schemaatBool('website.plans.show_prices'),
            'website.plans.cta_label' => $settings->get('website.plans.cta_label', 'Alege'),
            // Testimonials
            'website.testimonials.enabled' => $schemaatBool('website.testimonials.enabled', false),
            'website.testimonials.title' => $settings->get('website.testimonials.title', 'Povești de Succes'),
            'website.testimonials.items' => $schemaatJson('website.testimonials.items'),
            // Contact
            'website.contact.enabled' => $schemaatBool('website.contact.enabled'),
            'website.contact.title' => $settings->get('website.contact.title', 'Contact'),
            'website.contact.subtitle' => $settings->get('website.contact.subtitle', 'Te așteptăm!'),
            'website.contact.phone' => $settings->get('website.contact.phone', $settings->get('gym_phone')),
            'website.contact.email' => $settings->get('website.contact.email', $settings->get('gym_email')),
            'website.contact.address' => $settings->get('website.contact.address', $settings->get('gym_address')),
            'website.contact.form_enabled' => $schemaatBool('website.contact.form_enabled', false),
            // Footer
            'website.footer.text_left' => $settings->get('website.footer.text_left'),
            'website.footer.text_right' => $settings->get('website.footer.text_right', 'Linkuri Utile'),
            'website.footer.links' => $schemaatJson('website.footer.links'),
            'website.footer.copyright_text' => $settings->get('website.footer.copyright_text'),
            // Socials
            'website.footer.socials.facebook' => $settings->get('website.footer.socials.facebook', $settings->get('social_facebook')),
            'website.footer.socials.instagram' => $settings->get('website.footer.socials.instagram', $settings->get('social_instagram')),
            'website.footer.socials.tiktok' => $settings->get('website.footer.socials.tiktok', $settings->get('social_tiktok')),
            'website.footer.socials.youtube' => $settings->get('website.footer.socials.youtube'),
            'website.footer.socials.whatsapp' => $settings->get('website.footer.socials.whatsapp'),
        ];

        $this->form->fill(Arr::undot($fillData));
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Tabs::make('Website')
                    ->tabs([
                        Tabs\Tab::make('Design & Culori')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                TextInput::make('website.brand.name')
                                    ->label('Nume Brand (Afișat pe site)')
                                    ->required(),
                                FileUpload::make('website.brand.logo_url')
                                    ->label('Logo Site')
                                    ->image()
                                    ->disk('public')
                                    ->directory('branding'),
                                FileUpload::make('website.brand.favicon_url')
                                    ->label('Favicon (Iconiță Browser)')
                                    ->image()
                                    ->disk('public')
                                    ->directory('branding'),
                                ColorPicker::make('website.theme.primary_color')
                                    ->label('Culoare Principală'),
                                ColorPicker::make('website.theme.secondary_color')
                                    ->label('Culoare Secundară'),
                            ]),

                        Tabs\Tab::make('Meniu (Header)')
                            ->icon('heroicon-o-bars-3')
                            ->schema([
                                Repeater::make('website.header.nav.items')
                                    ->label('Linkuri Navigație')
                                    ->schema([
                                        TextInput::make('label')->required()->label('Titlu'),
                                        TextInput::make('href')
                                            ->label('Link Pagina / Extern')
                                            ->datalist([
                                                '#acasa',
                                                '#abonamente',
                                                '#contact',
                                                '/politica-confidentialitate',
                                                '/termeni-si-conditii'
                                            ])
                                            ->placeholder('Ex: #acasa sau https://...')
                                            ->required(),
                                        Toggle::make('visible')->label('Vizibil')->default(true)
                                    ])->columns(3)->collapsible(),
                                Section::make('Buton Principal (Header)')
                                    ->schema([
                                        TextInput::make('website.header.cta.primary.label')->label('Text Buton'),
                                        TextInput::make('website.header.cta.primary.href')
                                            ->label('Link Buton')
                                            ->datalist([
                                                '/app/login',
                                                '#acasa',
                                                '#abonamente',
                                                '#contact'
                                            ])
                                            ->placeholder('Ex: /app/login sau https://...'),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Hero Section')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Toggle::make('website.hero.enabled')->label('Afișează secțiunea Hero'),
                                TextInput::make('website.hero.title')->label('Titlu Principal'),
                                Textarea::make('website.hero.subtitle')->label('Subtitlu / Descriere'),
                                FileUpload::make('website.hero.image_url')->label('Imagine Hero')->image()->disk('public')->directory('website'),
                                Section::make('Buton 1')
                                    ->schema([
                                        TextInput::make('website.hero.primary_button.label')->label('Text'),
                                        TextInput::make('website.hero.primary_button.href')
                                            ->label('Link Buton Principal')
                                            ->datalist([
                                                '#abonamente',
                                                '#contact',
                                                '/app/login'
                                            ])
                                            ->placeholder('Ex: #abonamente'),
                                    ])->columns(2),
                                Section::make('Buton 2 (Opțional)')
                                    ->schema([
                                        TextInput::make('website.hero.secondary_button.label')->label('Text'),
                                        TextInput::make('website.hero.secondary_button.href')
                                            ->label('Link Buton Secundar')
                                            ->datalist([
                                                '#contact',
                                                '#abonamente',
                                                '/app/login'
                                            ])
                                            ->placeholder('Ex: #contact'),
                                    ])->columns(2),
                            ]),

                        Tabs\Tab::make('Facilități / Avantaje')
                            ->icon('heroicon-o-star')
                            ->schema([
                                Toggle::make('website.features.enabled')->label('Afișează secțiunea Facilități'),
                                TextInput::make('website.features.title')->label('Titlu Secțiune'),
                                Repeater::make('website.features.items')
                                    ->label('Avantaje')
                                    ->schema([
                                        TextInput::make('title')->label('Titlu')->required(),
                                        Textarea::make('text')->label('Descriere scurtă')->rows(2)->required(),
                                        TextInput::make('icon')->label('Iconiță (Emoji sau clasă)'),
                                        Toggle::make('visible')->label('Vizibil')->default(true)
                                    ])->columns(2)->collapsible(),
                            ]),

                        Tabs\Tab::make('Abonamente (Pricing)')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Toggle::make('website.plans.enabled')->label('Afișează secțiunea Abonamente'),
                                Toggle::make('website.plans.show_prices')->label('Afișează prețurile publice'),
                                TextInput::make('website.plans.title')->label('Titlu Secțiune'),
                                TextInput::make('website.plans.subtitle')->label('Subtitlu'),
                                TextInput::make('website.plans.cta_label')->label('Text Buton Cumpără (ex: Alege Planul)'),
                            ]),

                        Tabs\Tab::make('Testimoniale')
                            ->icon('heroicon-o-chat-bubble-bottom-center-text')
                            ->schema([
                                Toggle::make('website.testimonials.enabled')->label('Afișează Testimoniale'),
                                TextInput::make('website.testimonials.title')->label('Titlu Secțiune'),
                                Repeater::make('website.testimonials.items')
                                    ->label('Păreri Clienți')
                                    ->schema([
                                        TextInput::make('name')->label('Nume Client')->required(),
                                        TextInput::make('role')->label('Rol / Detalii (ex: Membru VIP)'),
                                        Textarea::make('text')->label('Mesajul clientului')->required(),
                                        FileUpload::make('photo_url')->label('Poza Profil')->image()->disk('public')->directory('website'),
                                        Toggle::make('visible')->default(true)
                                    ])->columns(2)->collapsible()
                            ]),

                        Tabs\Tab::make('Contact & Footer')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Toggle::make('website.contact.enabled')->label('Afișează Secțiunea Contact'),
                                TextInput::make('website.contact.title')->label('Titlu Contact'),
                                TextInput::make('website.contact.phone')->label('Telefon de afișat'),
                                TextInput::make('website.contact.email')->label('Email de afișat'),
                                TextInput::make('website.contact.address')->label('Adresă de afișat'),
                                
                                Section::make('Footer (Subsol)')
                                    ->schema([
                                        TextInput::make('website.footer.text_left')->label('Text Scurt Stânga'),
                                        TextInput::make('website.footer.copyright_text')->label('Copyright (ex: Toate drepturile rezervate)'),
                                        Repeater::make('website.footer.links')
                                            ->label('Linkuri Subsol (Ex: Termeni)')
                                            ->schema([
                                                TextInput::make('label')->required(),
                                                TextInput::make('href')->required(),
                                                Toggle::make('visible')->default(true)
                                            ])->columns(3)->collapsible()
                                    ]),

                                Section::make('Rețele Sociale')
                                    ->schema([
                                        TextInput::make('website.footer.socials.facebook')->label('Link Facebook')->url(),
                                        TextInput::make('website.footer.socials.instagram')->label('Link Instagram')->url(),
                                        TextInput::make('website.footer.socials.tiktok')->label('Link TikTok')->url(),
                                        TextInput::make('website.footer.socials.youtube')->label('Link Youtube')->url(),
                                        TextInput::make('website.footer.socials.whatsapp')->label('Număr WhatsApp (ex: 40700000000)'),
                                    ])->columns(2),
                            ]),
                    ])
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvează și Publică Website')
                ->color('primary')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            
            $keysToSave = [
                'website.brand.name', 'website.brand.logo_url', 'website.brand.favicon_url',
                'website.theme.primary_color', 'website.theme.secondary_color',
                'website.header.nav.items', 'website.header.cta.primary.label', 'website.header.cta.primary.href',
                'website.hero.enabled', 'website.hero.title', 'website.hero.subtitle', 'website.hero.image_url',
                'website.hero.primary_button.label', 'website.hero.primary_button.href',
                'website.hero.secondary_button.label', 'website.hero.secondary_button.href',
                'website.features.enabled', 'website.features.title', 'website.features.items',
                'website.plans.enabled', 'website.plans.title', 'website.plans.subtitle', 'website.plans.show_prices', 'website.plans.cta_label',
                'website.testimonials.enabled', 'website.testimonials.title', 'website.testimonials.items',
                'website.contact.enabled', 'website.contact.title', 'website.contact.subtitle', 'website.contact.phone',
                'website.contact.email', 'website.contact.address', 'website.contact.form_enabled',
                'website.footer.text_left', 'website.footer.text_right', 'website.footer.links', 'website.footer.copyright_text',
                'website.footer.socials.facebook', 'website.footer.socials.instagram', 'website.footer.socials.tiktok', 'website.footer.socials.youtube', 'website.footer.socials.whatsapp'
            ];

            foreach ($keysToSave as $key) {
                // Extract value from potentially nested Filament state
                $value = data_get($data, $key);
                
                // Determine type based on value
                $type = is_array($value) ? 'json' : (is_bool($value) ? 'bool' : 'string');
                $valToSave = is_array($value) ? json_encode($value) : $value;
                
                // Handle booleans manually since form states might be raw boolean
                if (is_bool($value)) $valToSave = $value ? '1' : '0';

                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $valToSave ?? '',
                        'type' => $type,
                        'group' => 'website',
                        'is_public' => true,
                    ]
                );
                
                Cache::forget('setting:' . $key);
            }
            
            // Cleanup the bad nested record if it exists
            Setting::where('key', 'website')->delete();

            Cache::forget('public_settings');
            Cache::forget('settings:public');
            Cache::forget('public:website');

            Notification::make()
                ->title('Website-ul a fost actualizat!')
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

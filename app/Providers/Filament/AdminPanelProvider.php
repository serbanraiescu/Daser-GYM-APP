<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('7xl')
            ->brandName(fn () => app(\App\Services\SettingsService::class)->get('website.brand.name', app(\App\Services\SettingsService::class)->get('gym_name', 'Gym Content Manager')))
            ->brandLogo(function () {
                $logo = app(\App\Services\SettingsService::class)->get('website.brand.logo_url', app(\App\Services\SettingsService::class)->get('gym_logo'));
                return $logo ? asset('storage/' . $logo) : null;
            })
            ->favicon(function () {
                $favicon = app(\App\Services\SettingsService::class)->get('website.brand.favicon_url');
                return $favicon ? asset('storage/' . $favicon) : null;
            })
            ->colors([
                'primary' => app(\App\Services\SettingsService::class)->get('website.theme.primary_color', app(\App\Services\SettingsService::class)->get('gym_primary_color', '#f59e0b')),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                \App\Http\Middleware\CheckLicense::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

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

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->login()
            ->brandName(fn () => app(\App\Services\SettingsService::class)->get('website.brand.name', app(\App\Services\SettingsService::class)->get('gym_name', 'Portal Membri')))
            ->brandLogo(function () {
                $logo = app(\App\Services\SettingsService::class)->get('website.brand.logo_url', app(\App\Services\SettingsService::class)->get('gym_logo'));
                return $logo ? asset('storage/' . $logo) : null;
            })
            ->favicon(function () {
                $favicon = app(\App\Services\SettingsService::class)->get('website.brand.favicon_url');
                return $favicon ? asset('storage/' . $favicon) : null;
            })
            ->colors([
                'primary' => app(\App\Services\SettingsService::class)->get('website.theme.primary_color', app(\App\Services\SettingsService::class)->get('gym_primary_color', '#3b82f6')),
            ])
            ->topNavigation() // Hide sidebar, use top nav
            ->maxContentWidth('7xl')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\Filament\App\Pages')
            ->pages([
                // We will rely on discovered pages or register our specific page
                \App\Filament\App\Pages\MemberDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\Filament\App\Widgets')
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
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->renderHook(
                'panels::footer',
                fn (): string => '<div class="text-center py-4 text-xs text-gray-400">
                    <p>Powered by <a href="https://daserdesign.ro" target="_blank" class="font-semibold hover:text-primary-500">Daser Technologies</a></p>
                    <p>&copy; ' . date('Y') . ' Daser Enterprise SRL</p>
                    <p>Licensed Software – All Rights Reserved</p>
                </div>',
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

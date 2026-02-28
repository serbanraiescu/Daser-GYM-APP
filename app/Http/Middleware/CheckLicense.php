<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenseService;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        // Don't check on migration, livewire uploads, or the settings page itself (so you can always activate)
        if ($request->is('force-migrate*') || 
            $request->is('livewire/upload*') || 
            $request->is('admin/manage-settings*')) {
            return $next($request);
        }

        $licenseService = app(LicenseService::class);
        $cache = $licenseService->checkLicense();

        $status = $cache['status'] ?? 'denied';
        $daysLeft = $cache['days_left'] ?? 0;
        $isGrace = $cache['is_grace_period'] ?? false;
        $graceDays = $cache['grace_days_left'] ?? 0;

        // CASE A: Hard Lockout
        if ($status === 'denied') {
            return response(view('components.license.lockout'));
        }

        // CASE C: Grace Period (Highest priority warning)
        if ($isGrace) {
             FilamentView::registerRenderHook(
                'panels::body.start',
                fn (): string => Blade::render('<x-license.grace-modal :days="$days" />', ['days' => $graceDays])
            );
        } 
        // CASE B: Warning Banner
        elseif ($status === 'active' && $daysLeft <= 15 && $daysLeft > 0) {
            FilamentView::registerRenderHook(
                'panels::body.start',
                fn (): string => Blade::render('<x-license.warning-banner :days="$days" />', ['days' => $daysLeft])
            );
        }

        return $next($request);
    }
}

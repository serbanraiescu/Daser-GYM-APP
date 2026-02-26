<x-filament-panels::page>
    <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
        
        <!-- Left Column: Membership Info & Payment -->
        <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; gap: 2rem;">
            
            <x-filament::section>
                <x-slot name="heading">
                    Salutare, {{ auth()->user()->name }}! 👋
                </x-slot>

                @if($activeMembership)
                    <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                        <div>
                            <span style="color: gray; font-size: 0.875rem;">Abonament Curent</span>
                            <h3 style="font-size: 1.25rem; font-weight: bold; margin: 0;">{{ $activeMembership->plan->name ?? 'Abonament Gym' }}</h3>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                            <div>
                                <span style="color: gray; font-size: 0.875rem;">Expiră la</span>
                                <p style="font-weight: bold; margin: 0;">{{ $activeMembership->expires_at->format('d M Y') }}</p>
                            </div>
                            <div style="text-align: right;">
                                <span style="color: gray; font-size: 0.875rem; display: block;">Stare</span>
                                <x-filament::badge color="success">Activ</x-filament::badge>
                            </div>
                        </div>
                    </div>
                @else
                    <div style="background-color: #FEF2F2; color: #991B1B; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <x-filament::icon icon="heroicon-o-exclamation-triangle" style="width: 1.5rem; height: 1.5rem;" />
                            <strong style="font-size: 1.1rem;">Nu ai un abonament activ</strong>
                        </div>
                        <p style="margin: 0; font-size: 0.9rem;">Te rugăm să îți reînnoiești abonamentul sau să ne contactezi la recepție pentru asistență.</p>
                    </div>
                @endif
            </x-filament::section>

            <!-- Payment Action -->
            <x-filament::section style="background-color: #111827; color: white;">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <x-filament::icon icon="heroicon-o-credit-card" style="width: 2rem; height: 2rem; color: white;" />
                    <div>
                        <h3 style="font-size: 1.25rem; font-weight: bold; margin: 0; color: white;">Plată Online</h3>
                        <p style="color: #9CA3AF; margin: 0; font-size: 0.875rem;">Reînnoiește rapid și sigur.</p>
                    </div>
                </div>
                <!-- Inactive Button -->
                <button disabled style="width: 100%; background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 0.75rem; border-radius: 0.5rem; opacity: 0.5; cursor: not-allowed; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <x-filament::icon icon="heroicon-m-lock-closed" style="width: 1.25rem; height: 1.25rem;" />
                    În curând - Plătește cu Cardul
                </button>
            </x-filament::section>

        </div>

        <!-- Right Column: Digital Card / QR -->
        <div style="flex: 1; min-width: 300px;">
            <x-filament::section style="height: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem;">Card de Acces Digital</h3>
                <p style="color: gray; font-size: 0.875rem; margin-bottom: 2rem;">Scanează acest cod la turnichetul de la intrare.</p>

                <div style="background: white; padding: 1rem; border-radius: 1rem; border: 1px solid #e5e7eb; display: inline-block; position: relative; margin-bottom: 1.5rem;">
                    <!-- Placeholder QR Code using SVG -->
                    <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 12rem; height: 12rem; color: #1F2937;">
                        <path fill-rule="evenodd" d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v2h-3v-2zm-3 0h2v2h-2v-2zm3 3h3v2h-3v-2zm-3 0h2v2h-2v-2zm3 3h3v2h-3v-2zm-3 0h2v2h-2v-2z" clip-rule="evenodd"/>
                    </svg>
                    
                    @if(!$activeMembership)
                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); border-radius: 1rem; display: flex; align-items: center; justify-content: center;">
                            <span style="background: #EF4444; color: white; font-weight: bold; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem;">ACCES BLOCAT</span>
                        </div>
                    @endif
                </div>

                <div style="font-family: monospace; font-size: 1.5rem; font-weight: bold; letter-spacing: 0.25em;">
                    {{ str_pad(auth()->user()->member_id ?? '000', 8, '0', STR_PAD_LEFT) }}
                </div>
            </x-filament::section>
        </div>

    </div>

    <!-- Custom Footer -->
    @php
        $settings = app(\App\Services\SettingsService::class);
        $phone = $settings->get('gym_phone');
        $email = $settings->get('gym_email');
        $facebook = $settings->get('social_facebook');
        $instagram = $settings->get('social_instagram');
        $tiktok = $settings->get('social_tiktok');
    @endphp

    <div style="margin-top: 3rem; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 2rem; color: #6B7280; font-size: 0.875rem;">
        
        <div style="margin-bottom: 1rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
            <strong style="color: #374151;">Ai nevoie de ajutor? Contactează recepția:</strong> 
            <div>
                @if($phone) <a href="tel:{{ $phone }}" style="color: #3b82f6; text-decoration: none; font-weight: 500;">{{ $phone }}</a> @endif
                @if($phone && $email) <span style="margin: 0 0.5rem; color: #d1d5db;">|</span> @endif
                @if($email) <a href="mailto:{{ $email }}" style="color: #3b82f6; text-decoration: none; font-weight: 500;">{{ $email }}</a> @endif
            </div>
        </div>
        
        @if($facebook || $instagram || $tiktok)
            <div style="display: flex; justify-content: center; gap: 1.5rem; margin-bottom: 2rem; margin-top: 1.5rem;">
                @if($facebook) 
                    <a href="{{ $facebook }}" target="_blank" style="color: #4B5563; display: flex; align-items: center; gap: 0.25rem; text-decoration: none; font-weight: 500;">
                        <x-filament::icon icon="heroicon-o-globe-alt" style="width: 1.5rem; height: 1.5rem;" /> Facebook
                    </a> 
                @endif
                @if($instagram) 
                    <a href="{{ $instagram }}" target="_blank" style="color: #4B5563; display: flex; align-items: center; gap: 0.25rem; text-decoration: none; font-weight: 500;">
                        <x-filament::icon icon="heroicon-o-camera" style="width: 1.5rem; height: 1.5rem;" /> Instagram
                    </a> 
                @endif
                @if($tiktok) 
                    <a href="{{ $tiktok }}" target="_blank" style="color: #4B5563; display: flex; align-items: center; gap: 0.25rem; text-decoration: none; font-weight: 500;">
                        <x-filament::icon icon="heroicon-o-play" style="width: 1.5rem; height: 1.5rem;" /> TikTok
                    </a> 
                @endif
            </div>
        @endif

        <div style="opacity: 0.6; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">
            Created by Daser design studio | Versiunea 1.0.0
        </div>
    </div>
</x-filament-panels::page>

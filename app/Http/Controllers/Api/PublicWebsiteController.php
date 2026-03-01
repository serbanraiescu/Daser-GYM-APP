<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PublicWebsiteController extends Controller
{
    /**
     * Get aggregate configuration for the React public landing page.
     */
    public function getWebsiteConfig(): JsonResponse
    {
        $config = Cache::remember('public:website_v4', now()->addHours(24), function () {
            // Fetch all settings starting with 'website.' and 'gym_' as fallback
            $settings = Setting::where('is_public', true)
                ->where(function ($query) {
                    $query->where('key', 'like', 'website.%')
                          ->orWhere('key', 'like', 'gym_%')
                          ->orWhere('key', 'like', 'social_%');
                })
                ->get()
                ->keyBy('key');

            $get = function ($key, $default = null) use ($settings) {
                if (!$settings->has($key)) return $default;
                
                $setting = $settings->get($key);
                $value = $setting->value;
                return match ($setting->type) {
                    'json' => json_decode($value, true) ?? [],
                    'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    'int' => (int) $value,
                    default => $value,
                };
            };

            $getUrl = function ($path) {
                if (empty($path)) return null;
                if (str_starts_with($path, 'http')) return $path;
                return str_starts_with($path, '/storage/') ? $path : '/storage/' . $path;
            };

            // Handle Plans synchronization
            $plansEnabled = $get('website.plans.enabled', true); // Default to true if not set
            $useDbPlans = $get('website.plans.use_db', true); // Default to true
            $planItems = [];

            if ($plansEnabled) {
                if ($useDbPlans) {
                    $planItems = \App\Models\Plan::where('active', true)
                        ->get()
                        ->map(function ($plan) {
                            return [
                                'name' => $plan->name,
                                'price' => (float) $plan->price,
                                'duration' => $plan->duration_days . ' zile',
                                'description' => $plan->description,
                                'features' => array_map(function($f) {
                                    return is_array($f) ? ($f['name'] ?? '') : $f;
                                }, $plan->features ?? []),
                                'is_featured' => false,
                            ];
                        })
                        ->toArray();
                } else {
                    // Fallback to manual items if needed (not currently in schema but for future proofing)
                    $planItems = $get('website.plans.items', []);
                }
            }

            return [
                'brand' => [
                    'name' => $get('website.brand.name', $get('gym_name', 'Gym App')),
                    'logo_url' => $getUrl($get('website.brand.logo_url', $get('gym_logo'))),
                    'favicon_url' => $getUrl($get('website.brand.favicon_url')),
                ],
                'theme' => [
                    'primary_color' => $get('website.theme.primary_color', $get('gym_primary_color', '#3b82f6')),
                    'secondary_color' => $get('website.theme.secondary_color', '#1e293b'),
                ],
                'header' => [
                    'nav_items' => $get('website.header.nav.items', [
                        ['label' => 'Acasă', 'href' => '#acasa', 'visible' => true],
                        ['label' => 'Abonamente', 'href' => '#abonamente', 'visible' => true],
                        ['label' => 'Contact', 'href' => '#contact', 'visible' => true],
                    ]),
                    'cta_primary' => [
                        'label' => $get('website.header.cta.primary.label', 'Autentificare'),
                        'href' => $get('website.header.cta.primary.href', '/app/login'),
                    ],
                ],
                'hero' => [
                    'enabled' => $get('website.hero.enabled', true),
                    'title' => $get('website.hero.title', 'Transformă-ți Corpul Astăzi.'),
                    'subtitle' => $get('website.hero.subtitle', $get('gym_description', '')),
                    'primary_button' => [
                        'label' => $get('website.hero.primary_button.label', 'Vezi Abonamentele'),
                        'href' => $get('website.hero.primary_button.href', '#abonamente'),
                    ],
                    'secondary_button' => [
                        'label' => $get('website.hero.secondary_button.label', 'Contactează-ne'),
                        'href' => $get('website.hero.secondary_button.href', '#contact'),
                    ],
                    'image_url' => $getUrl($get('website.hero.image_url', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=2070')),
                    'background_url' => $getUrl($get('website.hero.background_url')),
                ],
                'features' => [
                    'enabled' => $get('website.features.enabled', true),
                    'title' => $get('website.features.title', 'De ce să ne alegi pe noi?'),
                    'items' => $get('website.features.items', [
                        ['title' => 'Locația Noastră', 'text' => $get('gym_address', 'Adresă Gym'), 'icon' => '📍', 'visible' => true],
                        ['title' => 'Program', 'text' => $get('gym_hours', 'L-V: 07-22'), 'icon' => '🕒', 'visible' => true],
                        ['title' => 'Contact Rapid', 'text' => $get('gym_phone', '0700 000 000'), 'icon' => '📞', 'visible' => true],
                    ]),
                ],
                'plans' => [
                    'enabled' => $get('website.plans.enabled', true),
                    'title' => $get('website.plans.title', 'Planuri de Membership'),
                    'subtitle' => $get('website.plans.subtitle', 'Alege abonamentul care se potrivește nevoilor tale.'),
                    'show_prices' => $get('website.plans.show_prices', true),
                    'cta_label' => $get('website.plans.cta_label', 'Alege Planul'),
                    'items' => $planItems,
                ],
                'testimonials' => [
                    'enabled' => $get('website.testimonials.enabled', false),
                    'title' => $get('website.testimonials.title', 'Povești de Succes'),
                    'items' => array_map(function($item) use ($getUrl) {
                        if (isset($item['photo_url'])) {
                            $item['photo_url'] = $getUrl($item['photo_url']);
                        }
                        return $item;
                    }, $get('website.testimonials.items', [])),
                ],
                'contact' => [
                    'enabled' => $get('website.contact.enabled', true),
                    'title' => $get('website.contact.title', 'Contact'),
                    'subtitle' => $get('website.contact.subtitle', 'Suntem aici pentru tine.'),
                    'phone' => $get('website.contact.phone', $get('gym_phone')),
                    'email' => $get('website.contact.email', $get('gym_email')),
                    'address' => $get('website.contact.address', $get('gym_address')),
                    'lat' => $get('website.contact.lat'),
                    'lng' => $get('website.contact.lng'),
                    'form_enabled' => $get('website.contact.form_enabled', false),
                    'map_embed_url' => $get('website.contact.map_embed_url'),
                ],
                'footer' => [
                    'text_left' => $get('website.footer.text_left', 'Misiunea noastră este să oferim un mediu premium și inspirațional.'),
                    'text_right' => $get('website.footer.text_right', 'Informații'),
                    'static_links' => $get('website.footer.links', [
                        ['label' => 'Acasă', 'href' => '/#acasa', 'visible' => true],
                        ['label' => 'Politica de Confidențialitate', 'href' => '/politica-confidentialitate', 'visible' => true],
                        ['label' => 'Termeni și Condiții', 'href' => '/termeni-si-conditii', 'visible' => true],
                    ]),
                    'socials' => $get('website.footer.socials', [
                        'facebook' => $get('social_facebook'),
                        'instagram' => $get('social_instagram'),
                        'tiktok' => $get('social_tiktok'),
                        'youtube' => null,
                        'whatsapp' => null,
                    ]),
                    'copyright_text' => $get('website.footer.copyright_text', 'Toate drepturile rezervate.'),
                ],
            ];
        });

        // Merge dynamic pages OUTSIDE the cache to ensure instant updates
        $config['footer']['links'] = array_merge(
            $config['footer']['static_links'] ?? [],
            \App\Models\Page::where('is_active', true)
                ->where('show_in_footer', true)
                ->get()
                ->map(function($page) {
                    return ['label' => $page->title, 'href' => '/p/' . $page->slug, 'visible' => true];
                })
                ->toArray()
        );

        unset($config['footer']['static_links']);

        // Load version from JSON
        $version = '0.0.0';
        $versionFile = base_path('version.json');
        if (file_exists($versionFile)) {
            $vData = json_decode(file_get_contents($versionFile), true);
            $version = $vData['version'] ?? $version;
        }

        return response()->json(array_merge($config, ['app_version' => $version]));
    }
}

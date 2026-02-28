<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        // Fetch settings for global elements
        $settings = Setting::where('is_public', true)
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

        $brandName = $get('website.brand.name', $get('gym_name', 'Daser Gym'));
        $logo = $get('website.brand.logo_url', $get('gym_logo'));
        $primaryColor = $get('website.theme.primary_color', $get('gym_primary_color', '#3b82f6'));
        $secondaryColor = $get('website.theme.secondary_color', '#1e293b');
        
        $navItems = $get('website.header.nav.items', [
            ['label' => 'Acasă', 'href' => '/#acasa', 'visible' => true],
            ['label' => 'Abonamente', 'href' => '/#abonamente', 'visible' => true],
            ['label' => 'Contact', 'href' => '/#contact', 'visible' => true],
        ]);

        $footerText = $get('website.footer.text_left', 'Misiunea noastră este să oferim un mediu premium și inspirațional.');
        $copyright = $get('website.footer.copyright_text', 'Toate drepturile rezervate.');
        
        $staticFooterLinks = $get('website.footer.links', [
            ['label' => 'Acasă', 'href' => '/#acasa', 'visible' => true],
        ]);

        $dynamicFooterLinks = Page::where('is_active', true)
            ->where('show_in_footer', true)
            ->get()
            ->map(function($p) {
                return ['label' => $p->title, 'href' => '/p/' . $p->slug, 'visible' => true];
            })
            ->toArray();

        $footerLinks = array_merge($staticFooterLinks, $dynamicFooterLinks);

        $socials = [
            'facebook' => $get('social_facebook'),
            'instagram' => $get('social_instagram'),
            'tiktok' => $get('social_tiktok'),
        ];

        // Process FAQ JSON into Schema
        $faqSchema = null;
        if (!empty($page->faq_data) && is_array($page->faq_data) && count($page->faq_data) > 0) {
            $mainEntity = [];
            foreach ($page->faq_data as $qa) {
                if (isset($qa['question']) && isset($qa['answer'])) {
                    $mainEntity[] = [
                        "@type" => "Question",
                        "name" => strip_tags($qa['question']),
                        "acceptedAnswer" => [
                            "@type" => "Answer",
                            "text" => strip_tags($qa['answer'])
                        ]
                    ];
                }
            }
            if (count($mainEntity) > 0) {
                $faqSchema = [
                    "@context" => "https://schema.org",
                    "@type" => "FAQPage",
                    "mainEntity" => $mainEntity
                ];
            }
        }

        // Base Page Schema
        $pageSchema = [
            "@context" => "https://schema.org",
            "@type" => $page->schema_type ?? "WebPage",
            "name" => $page->title,
            "description" => $page->meta_description ?? substr(strip_tags($page->content), 0, 160)
        ];

        return view('pages.show', compact(
            'page', 'brandName', 'logo', 'primaryColor', 'secondaryColor', 
            'navItems', 'footerText', 'footerLinks', 'socials', 'copyright', 
            'faqSchema', 'pageSchema'
        ));
    }
}

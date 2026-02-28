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
        
        // Fetch branding for the HTML layout
        $brandName = Setting::where('key', 'gym_name')->first()?->value ?? 'Daser Gym';
        $logo = Setting::where('key', 'gym_logo')->first()?->value;
        $primaryColor = Setting::where('key', 'gym_primary_color')->first()?->value ?? '#3b82f6';
        $socials = [
            'facebook' => Setting::where('key', 'social_facebook')->first()?->value,
            'instagram' => Setting::where('key', 'social_instagram')->first()?->value,
            'tiktok' => Setting::where('key', 'social_tiktok')->first()?->value,
        ];
        $copyright = Setting::where('key', 'website.footer.copyright_text')->first()?->value ?? 'Toate drepturile rezervate.';

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
                            "text" => strip_tags($qa['answer']) // Strip tags to ensure valid JSON string for schema
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

        return view('pages.show', compact('page', 'brandName', 'logo', 'primaryColor', 'socials', 'copyright', 'faqSchema', 'pageSchema'));
    }
}

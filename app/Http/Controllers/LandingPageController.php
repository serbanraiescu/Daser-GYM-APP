<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    protected $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    public function index()
    {
        return view('welcome', $this->getCommonData());
    }

    public function privacy()
    {
        $data = $this->getCommonData();
        $data['title'] = 'Politica de Confidențialitate';
        $data['content'] = $this->settings->get('legal_privacy', '<p>Text standard...</p>');
        
        return view('legal', $data);
    }

    public function terms()
    {
        $data = $this->getCommonData();
        $data['title'] = 'Termeni și Condiții';
        $data['content'] = $this->settings->get('legal_terms', '<p>Text standard...</p>');
        
        return view('legal', $data);
    }

    private function getCommonData(): array
    {
        return [
            'gymName' => $this->settings->get('gym_name', 'Gym App'),
            'gymDescription' => $this->settings->get('gym_description', ''),
            'gymLogo' => $this->settings->get('gym_logo', ''),
            'primaryColor' => $this->settings->get('gym_primary_color', '#3b82f6'),
            'address' => $this->settings->get('gym_address', ''),
            'phone' => $this->settings->get('gym_phone', ''),
            'email' => $this->settings->get('gym_email', ''),
            'hours' => $this->settings->get('gym_hours', ''),
            'facebook' => $this->settings->get('social_facebook', ''),
            'instagram' => $this->settings->get('social_instagram', ''),
            'tiktok' => $this->settings->get('social_tiktok', ''),
        ];
    }
}

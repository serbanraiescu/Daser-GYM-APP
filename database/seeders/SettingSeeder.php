<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Branding
            [
                'key' => 'gym_name',
                'value' => 'Daser Gym',
                'type' => 'string',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'gym_logo',
                'value' => null,
                'type' => 'image',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'gym_primary_color',
                'value' => '#3b82f6',
                'type' => 'string',
                'group' => 'branding',
                'is_public' => true,
            ],
            
            // Business Rules
            [
                'key' => 'currency',
                'value' => 'RON',
                'type' => 'string',
                'group' => 'business',
                'is_public' => true,
            ],
            [
                'key' => 'grace_days',
                'value' => '3',
                'type' => 'integer',
                'group' => 'business',
                'is_public' => false,
            ],
            [
                'key' => 'timezone',
                'value' => 'Europe/Bucharest',
                'type' => 'string',
                'group' => 'business',
                'is_public' => true,
            ],

            // Templates
            [
                'key' => 'sms_welcome_template',
                'value' => 'Welcome to {gym_name}, {member_name}! Your membership is active until {expiry_date}.',
                'type' => 'string',
                'group' => 'templates',
                'is_public' => false,
            ],
            [
                'key' => 'email_welcome_subject',
                'value' => 'Welcome to the family!',
                'type' => 'string',
                'group' => 'templates',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}

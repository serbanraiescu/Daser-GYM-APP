<?php

namespace Database\Seeders;

use App\Models\LoyaltyProgram;
use App\Models\Member;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Promotion;
use App\Models\PromotionCondition;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Admin User
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 0.1 Plan Features (Romanian)
        $features = [
            ['slug' => 'acces_sala', 'name' => 'Acces Complet Sală', 'description' => 'Acces nelimitat la echipamentele de fitness și forță.'],
            ['slug' => 'clase_grup', 'name' => 'Clase de Grup', 'description' => 'Participare la sesiuni de Yoga, Pilates, Zumba sau HIIT.'],
            ['slug' => 'sauna', 'name' => 'Acces Saună', 'description' => 'Relaxare la saună după antrenament.'],
            ['slug' => 'evaluare_initiala', 'name' => 'Evaluare Inițială', 'description' => 'Întâlnire cu un antrenor pentru stabilirea obiectivelor.'],
            ['slug' => 'fidelitate', 'name' => 'Program Fidelitate', 'description' => 'Acumulare de puncte pentru reduceri viitoare.'],
        ];

        foreach ($features as $f) {
            PlanFeature::updateOrCreate(['slug' => $f['slug']], $f);
        }
        // 1. Settings
        Setting::updateOrCreate(
            ['key' => 'gym_name'],
            [
                'value' => 'Daser Elite Gym',
                'type' => 'string',
                'group' => 'branding',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'gym_description'],
            [
                'value' => 'Transformă-ți corpul și mintea la cea mai modernă sală de fitness din oraș. Echipamente de top, antrenori certificați și o comunitate pasionată.',
                'type' => 'string',
                'group' => 'public',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'gym_address'],
            [
                'value' => 'Strada Exemplului Nr. 10, București, România',
                'type' => 'string',
                'group' => 'public',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'gym_phone'],
            [
                'value' => '+40 700 000 000',
                'type' => 'string',
                'group' => 'public',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'gym_email'],
            [
                'value' => 'contact@dasergym.ro',
                'type' => 'string',
                'group' => 'public',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'gym_hours'],
            [
                'value' => 'Luni - Vineri: 07:00 - 22:00 | Sâmbătă: 09:00 - 18:00 | Duminică: Închis',
                'type' => 'string',
                'group' => 'public',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'legal_terms'],
            [
                'value' => '<h1>Termeni și Condiții</h1><p>Acesta este textul standard pentru termeni și condiții...</p>',
                'type' => 'string',
                'group' => 'legal',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'legal_privacy'],
            [
                'value' => '<h1>Politica de Confidențialitate</h1><p>Informații despre cum prelucrăm datele tale cu caracter personal...</p>',
                'type' => 'string',
                'group' => 'legal',
                'is_public' => true,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'grace_days_default'],
            [
                'value' => '7',
                'type' => 'int',
                'group' => 'business',
                'is_public' => false,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'sms_gateway_link'],
            [
                'value' => 'https://abistro.ro/api/sms/gateway/' . bin2hex(random_bytes(8)),
                'type' => 'string',
                'group' => 'sms',
                'is_public' => false,
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'sms_gateway_status'],
            [
                'value' => 'Așteptare conexiune Android...',
                'type' => 'string',
                'group' => 'sms',
                'is_public' => false,
            ]
        );

        // 1.5 Website Settings
        $websiteSettings = [
            'website.theme.primary_color' => ['type' => 'string', 'value' => '#3b82f6'],
            'website.theme.secondary_color' => ['type' => 'string', 'value' => '#1e293b'],
            'website.header.nav.items' => ['type' => 'json', 'value' => json_encode([
                ['label' => 'Acasă', 'href' => '#acasa', 'visible' => true],
                ['label' => 'Abonamente', 'href' => '#abonamente', 'visible' => true],
                ['label' => 'Contact', 'href' => '#contact', 'visible' => true],
            ])],
            'website.hero.title' => ['type' => 'string', 'value' => 'Transformă-ți Corpul Astăzi.'],
            'website.hero.subtitle' => ['type' => 'string', 'value' => 'Accesează echipamente de ultimă generație și programe personalizate.'],
            'website.hero.primary_button.label' => ['type' => 'string', 'value' => 'Vezi Abonamentele'],
            'website.hero.primary_button.href' => ['type' => 'string', 'value' => '#abonamente'],
            'website.features.title' => ['type' => 'string', 'value' => 'De ce să ne alegi pe noi?'],
            'website.features.items' => ['type' => 'json', 'value' => json_encode([
                ['title' => 'Locația Noastră', 'text' => 'Strada Exemplului, Nr. 10', 'icon' => '📍', 'visible' => true],
                ['title' => 'Program', 'text' => 'L-V: 07-22', 'icon' => '🕒', 'visible' => true],
                ['title' => 'Contact Rapid', 'text' => '0700 000 000', 'icon' => '📞', 'visible' => true]
            ])],
            'website.testimonials.enabled' => ['type' => 'bool', 'value' => '1'],
            'website.testimonials.title' => ['type' => 'string', 'value' => 'Povești de Succes'],
            'website.testimonials.items' => ['type' => 'json', 'value' => json_encode([
                ['name' => 'Andrei Popescu', 'role' => 'Membru VIP', 'text' => 'Cea mai bună sală din oraș, antrenori excepționali!', 'photo_url' => '', 'visible' => true],
                ['name' => 'Maria Ionescu', 'role' => 'Membru Standard', 'text' => 'Echipamentele sunt mereu curate și atmosfera este super.', 'photo_url' => '', 'visible' => true],
            ])],
            'website.footer.text_left' => ['type' => 'string', 'value' => 'Inspirație pentru un stil de viață sănătos.'],
            'website.footer.copyright_text' => ['type' => 'string', 'value' => 'Toate drepturile rezervate.']
        ];

        foreach ($websiteSettings as $k => $cfg) {
            Setting::updateOrCreate(
                ['key' => $k],
                [
                    'value' => $cfg['value'],
                    'type' => $cfg['type'],
                    'group' => 'website',
                    'is_public' => true,
                ]
            );
        }

        // 2. Plans
        $monthly = Plan::updateOrCreate(
            ['name' => 'Monthly Standard'],
            [
                'price' => 200,
                'duration_days' => 30,
                'active' => true,
            ]
        );

        $annual = Plan::updateOrCreate(
            ['name' => 'Annual VIP'],
            [
                'price' => 1800,
                'duration_days' => 365,
                'grace_days_override' => 14,
                'active' => true,
            ]
        );

        // 3. Members
        $john = Member::updateOrCreate(
            ['email' => 'john@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '0712345678',
                'category' => 'default',
                'status' => 'active',
            ]
        );

        $jane = Member::updateOrCreate(
            ['email' => 'jane@example.com'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'phone' => '0722334455',
                'category' => 'student',
                'status' => 'active',
            ]
        );

        // 4. Promotions
        $welcome = Promotion::updateOrCreate(
            ['name' => 'Welcome 10%'],
            [
                'type' => 'PERCENT',
                'value' => 10,
                'priority' => 10,
                'stacking_mode' => 'STACKABLE',
                'active' => true,
            ]
        );

        $studentPromo = Promotion::updateOrCreate(
            ['name' => 'Student Special 20%'],
            [
                'type' => 'PERCENT',
                'value' => 20,
                'priority' => 20,
                'stacking_mode' => 'EXCLUSIVE_GROUP',
                'exclusive_group' => 'category_discount',
                'active' => true,
            ]
        );

        PromotionCondition::updateOrCreate(
            ['promotion_id' => $studentPromo->id, 'field' => 'MEMBER_CATEGORY'],
            [
                'operator' => 'EQUALS',
                'value' => 'student',
            ]
        );

        $bundle = Promotion::updateOrCreate(
            ['name' => 'Buy 5 Get 1 Free'],
            [
                'type' => 'BUNDLE',
                'bundle_x' => 5,
                'bundle_y' => 1,
                'priority' => 5,
                'stacking_mode' => 'STACKABLE',
                'active' => true,
            ]
        );

        // 5. Loyalty Program
        LoyaltyProgram::updateOrCreate(
            ['name' => 'Renewal Rewards'],
            [
                'buy_x' => 5,
                'get_y' => 1,
                'eligible_plan_ids' => [$monthly->id],
                'reward_type' => 'FREE_RENEWAL',
                'active' => true,
            ]
        );

        // 6. Member Users
        User::updateOrCreate(
            ['email' => 'member@example.com'],
            [
                'name' => $john->full_name,
                'password' => Hash::make('password'),
                'role' => 'member',
                'member_id' => $john->id,
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\PlatformSetting;
use Illuminate\Database\Seeder;

class PlatformSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'platform_name',          'value' => 'Ticko-Plug',          'type' => 'string',  'group' => 'general',  'label' => 'Platform Name',           'is_public' => true],
            ['key' => 'platform_tagline',        'value' => 'Plug Into The Vibe',  'type' => 'string',  'group' => 'general',  'label' => 'Platform Tagline',        'is_public' => true],
            ['key' => 'support_email',           'value' => 'support@ticko-plug.co.ke', 'type' => 'string', 'group' => 'general', 'label' => 'Support Email',         'is_public' => true],
            ['key' => 'support_phone',           'value' => '+254700000000',        'type' => 'string',  'group' => 'general',  'label' => 'Support Phone',           'is_public' => true],
            ['key' => 'currency',                'value' => 'KES',                 'type' => 'string',  'group' => 'general',  'label' => 'Currency',                'is_public' => true],
            ['key' => 'timezone',                'value' => 'Africa/Nairobi',       'type' => 'string',  'group' => 'general',  'label' => 'Timezone',                'is_public' => false],
            ['key' => 'max_tickets_per_order',   'value' => '10',                  'type' => 'integer', 'group' => 'general',  'label' => 'Max Tickets Per Order',   'is_public' => true],
            ['key' => 'cart_expiry_minutes',     'value' => '15',                  'type' => 'integer', 'group' => 'general',  'label' => 'Cart Expiry (minutes)',   'is_public' => false],
            // Fees
            ['key' => 'platform_fee_percentage', 'value' => '5',                   'type' => 'decimal', 'group' => 'fees',     'label' => 'Platform Fee (%)',        'is_public' => true],
            ['key' => 'platform_fee_fixed',      'value' => '30',                  'type' => 'decimal', 'group' => 'fees',     'label' => 'Platform Fee Fixed (KES)','is_public' => true],
            ['key' => 'free_event_fee',          'value' => '0',                   'type' => 'decimal', 'group' => 'fees',     'label' => 'Free Event Fee (KES)',    'is_public' => true],
            // Payouts
            ['key' => 'payout_hold_days',        'value' => '7',                   'type' => 'integer', 'group' => 'payouts',  'label' => 'Payout Hold Days',        'is_public' => false],
            ['key' => 'min_payout_amount',       'value' => '1000',                'type' => 'decimal', 'group' => 'payouts',  'label' => 'Min Payout Amount (KES)', 'is_public' => false],
            // Features
            ['key' => 'enable_waitlist',         'value' => 'true',                'type' => 'boolean', 'group' => 'features', 'label' => 'Enable Waitlist',         'is_public' => false],
            ['key' => 'enable_reviews',          'value' => 'true',                'type' => 'boolean', 'group' => 'features', 'label' => 'Enable Reviews',          'is_public' => false],
            ['key' => 'enable_social_login',     'value' => 'false',               'type' => 'boolean', 'group' => 'features', 'label' => 'Enable Social Login',     'is_public' => false],
            ['key' => 'event_moderation',        'value' => 'false',               'type' => 'boolean', 'group' => 'features', 'label' => 'Require Event Moderation','is_public' => false],
        ];

        foreach ($settings as $setting) {
            PlatformSetting::firstOrCreate(
                ['key' => $setting['key']],
                array_merge($setting, ['description' => null])
            );
        }

        $this->command->info('Platform settings seeded successfully.');
    }
}

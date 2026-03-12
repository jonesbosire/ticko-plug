<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Music & Concerts',     'icon' => 'heroicon-o-musical-note',    'color' => '#7C3AED', 'description' => 'Live music, concerts, and DJ nights'],
            ['name' => 'Comedy & Entertainment','icon' => 'heroicon-o-face-smile',      'color' => '#F97316', 'description' => 'Stand-up comedy, improv, and entertainment shows'],
            ['name' => 'Sports & Fitness',      'icon' => 'heroicon-o-trophy',          'color' => '#22C55E', 'description' => 'Sports events, marathons, and fitness activities'],
            ['name' => 'Food & Drink',          'icon' => 'heroicon-o-cake',            'color' => '#EF4444', 'description' => 'Food festivals, wine tastings, and culinary events'],
            ['name' => 'Arts & Theatre',        'icon' => 'heroicon-o-paint-brush',     'color' => '#EC4899', 'description' => 'Theatre, dance, art exhibitions, and cultural events'],
            ['name' => 'Tech & Business',       'icon' => 'heroicon-o-computer-desktop','color' => '#0EA5E9', 'description' => 'Tech conferences, startup events, and business networking'],
            ['name' => 'Faith & Religion',      'icon' => 'heroicon-o-sparkles',        'color' => '#F0C427', 'description' => 'Gospel concerts, church events, and faith-based gatherings'],
            ['name' => 'Festivals & Culture',   'icon' => 'heroicon-o-star',            'color' => '#A855F7', 'description' => 'Cultural festivals, carnivals, and community celebrations'],
            ['name' => 'Nightlife & Parties',   'icon' => 'heroicon-o-moon',            'color' => '#1D4ED8', 'description' => 'Club nights, house parties, and social gatherings'],
            ['name' => 'Education & Training',  'icon' => 'heroicon-o-academic-cap',    'color' => '#64748B', 'description' => 'Workshops, seminars, and training sessions'],
            ['name' => 'Charity & Fundraisers', 'icon' => 'heroicon-o-heart',           'color' => '#BE185D', 'description' => 'Charity galas, fundraisers, and harambees'],
            ['name' => 'Conferences & Summits', 'icon' => 'heroicon-o-building-office', 'color' => '#0F766E', 'description' => 'Industry conferences, panels, and summits'],
        ];

        foreach ($categories as $index => $data) {
            Category::firstOrCreate(
                ['slug' => \Str::slug($data['name'])],
                array_merge($data, ['sort_order' => $index + 1, 'is_active' => true])
            );
        }

        $this->command->info('Categories seeded successfully.');
    }
}

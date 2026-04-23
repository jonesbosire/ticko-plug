<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Models\Category;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoEventSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = User::where('email', 'organizer@demo.co.ke')->firstOrFail();
        $categories = Category::all()->keyBy('slug');

        // Create venues
        $venues = $this->createVenues();

        // Demo events data
        $events = [
            [
                'title'          => 'Nairobi Jazz Festival 2026',
                'tagline'        => 'Three nights of world-class jazz under the Nairobi stars',
                'description'    => '<p>The biggest jazz festival East Africa has ever seen. Featuring over 30 artists across three stages, artisan food stalls, and a curated craft cocktail experience. This is the event of the year.</p><p>Doors open at 5 PM. First act on stage at 6 PM. Last act wraps at midnight.</p>',
                'start_datetime' => now()->addDays(14)->setTime(18, 0),
                'end_datetime'   => now()->addDays(14)->setTime(23, 59),
                'status'         => EventStatus::Published,
                'visibility'     => 'public',
                'featured_at'    => now(),
                'category_slug'  => 'music',
                'venue_key'      => 'uhuru',
                'tickets'        => [
                    ['name' => 'Early Bird', 'price' => 1500, 'qty' => 200],
                    ['name' => 'Regular',    'price' => 2500, 'qty' => 500],
                    ['name' => 'VIP',        'price' => 6000, 'qty' => 50],
                ],
            ],
            [
                'title'          => 'Churchill Live: Homeboyz Edition',
                'tagline'        => 'Kenya\'s top comedians bring the heat this weekend',
                'description'    => '<p>Churchill Live returns with a stacked lineup of Kenya\'s funniest comics. Expect 90 minutes of non-stop laughs, surprise guest appearances, and audience interaction. Bring your whole crew!</p>',
                'start_datetime' => now()->addDays(7)->setTime(19, 30),
                'end_datetime'   => now()->addDays(7)->setTime(22, 0),
                'status'         => EventStatus::Published,
                'visibility'     => 'public',
                'featured_at'    => now()->subMinutes(5),
                'category_slug'  => 'comedy',
                'venue_key'      => 'sarit',
                'tickets'        => [
                    ['name' => 'Standard',   'price' => 1000, 'qty' => 300],
                    ['name' => 'Premium',    'price' => 2000, 'qty' => 100],
                    ['name' => 'VVIP Table', 'price' => 15000, 'qty' => 10, 'max_per_order' => 10],
                ],
            ],
            [
                'title'          => 'Tech Summit Nairobi 2026',
                'tagline'        => 'Africa\'s fastest-growing tech conference',
                'description'    => '<p>Two days of cutting-edge talks on AI, fintech, and the African internet economy. Network with 500+ founders, investors, and developers from across the continent.</p><p>Workshops available on Day 2 — register early, slots are limited.</p>',
                'start_datetime' => now()->addDays(21)->setTime(8, 0),
                'end_datetime'   => now()->addDays(22)->setTime(17, 0),
                'status'         => EventStatus::Published,
                'visibility'     => 'public',
                'featured_at'    => now()->subMinutes(10),
                'category_slug'  => 'conferences',
                'venue_key'      => 'kiccc',
                'tickets'        => [
                    ['name' => 'Day 1 Only',    'price' => 3500, 'qty' => 250],
                    ['name' => 'Full Access',   'price' => 5500, 'qty' => 200],
                    ['name' => 'Workshop Pass', 'price' => 8000, 'qty' => 60],
                    ['name' => 'Startup Booth', 'price' => 25000, 'qty' => 15, 'max_per_order' => 1],
                ],
            ],
            [
                'title'          => 'Blankets & Wine Nairobi',
                'tagline'        => 'Spread your blanket, pop a bottle, vibe to great music',
                'description'    => '<p>The iconic Blankets & Wine returns to the lawns of Nairobi. Bring your blanket, your favourite people, and get ready to discover incredible African music acts alongside curated wines and craft beverages.</p>',
                'start_datetime' => now()->addDays(10)->setTime(14, 0),
                'end_datetime'   => now()->addDays(10)->setTime(20, 0),
                'status'         => EventStatus::Published,
                'visibility'     => 'public',
                'featured_at'    => now()->subMinutes(2),
                'category_slug'  => 'music',
                'venue_key'      => 'uhuru',
                'tickets'        => [
                    ['name' => 'General',  'price' => 2000, 'qty' => 400],
                    ['name' => 'Premium',  'price' => 4000, 'qty' => 100],
                ],
            ],
            [
                'title'          => 'Nairobi Half Marathon 2026',
                'tagline'        => 'Run the city. Own the streets.',
                'description'    => '<p>Kenya\'s most prestigious road race returns. Whether you\'re an elite runner chasing a PB or a first-timer just looking to finish, Nairobi Half Marathon has a category for you. Scenic route through the city\'s iconic streets.</p>',
                'start_datetime' => now()->addDays(30)->setTime(6, 30),
                'end_datetime'   => now()->addDays(30)->setTime(12, 0),
                'status'         => EventStatus::Published,
                'visibility'     => 'public',
                'featured_at'    => null,
                'category_slug'  => 'sports',
                'venue_key'      => 'uhuru',
                'tickets'        => [
                    ['name' => '5KM Fun Run',  'price' => 500,  'qty' => 1000],
                    ['name' => '21KM Half',    'price' => 1500, 'qty' => 2000],
                    ['name' => 'Elite Entry',  'price' => 0,    'qty' => 50],
                ],
            ],
            [
                'title'          => 'Afrofusion Night: East Meets West',
                'tagline'        => 'Amapiano. Afrobeats. Gengetone. One stage.',
                'description'    => '<p>A night where Africa\'s hottest sounds collide. Resident DJs and live performances across two rooms — upstairs is Amapiano & Afrobeats, downstairs is pure Gengetone. Dress code: Afro glam.</p>',
                'start_datetime' => now()->addDays(5)->setTime(21, 0),
                'end_datetime'   => now()->addDays(6)->setTime(4, 0),
                'status'         => EventStatus::Published,
                'visibility'     => 'public',
                'featured_at'    => now()->subMinutes(1),
                'category_slug'  => 'nightlife',
                'venue_key'      => 'sarit',
                'tickets'        => [
                    ['name' => 'Early Entry (Before 11PM)', 'price' => 800,  'qty' => 100],
                    ['name' => 'Standard Entry',             'price' => 1500, 'qty' => 300],
                    ['name' => 'VIP Table (6 pax)',          'price' => 12000, 'qty' => 20, 'max_per_order' => 1],
                ],
            ],
        ];

        foreach ($events as $data) {
            $venue    = $venues[$data['venue_key']] ?? null;
            $category = $categories[$data['category_slug']] ?? $categories->first();

            $slug = Str::slug($data['title']);
            if (Event::where('slug', $slug)->exists()) {
                $this->command->warn("Skipping '{$data['title']}' — already exists.");
                continue;
            }

            $event = Event::create([
                'organizer_id'   => $organizer->id,
                'category_id'    => $category->id,
                'venue_id'       => $venue?->id,
                'title'          => $data['title'],
                'slug'           => $slug,
                'tagline'        => $data['tagline'],
                'description'    => $data['description'],
                'start_datetime' => $data['start_datetime'],
                'end_datetime'   => $data['end_datetime'],
                'timezone'       => 'Africa/Nairobi',
                'status'         => $data['status'],
                'visibility'     => $data['visibility'],
                'featured_at'    => $data['featured_at'],
                'published_at'   => now(),
                'created_by'     => $organizer->id,
                'tags'           => [],
            ]);

            foreach ($data['tickets'] as $tt) {
                TicketType::create([
                    'event_id'          => $event->id,
                    'name'              => $tt['name'],
                    'price'             => $tt['price'],
                    'quantity_total'    => $tt['qty'],
                    'quantity_sold'     => 0,
                    'quantity_reserved' => 0,
                    'min_per_order'     => 1,
                    'max_per_order'     => $tt['max_per_order'] ?? 6,
                    'is_visible'        => true,
                    'sort_order'        => 0,
                ]);
            }

            $this->command->info("Created: {$data['title']}");
        }

        $this->command->info('Demo events seeded successfully!');
    }

    private function createVenues(): array
    {
        $venueData = [
            'uhuru' => [
                'name'          => 'Uhuru Gardens',
                'address_line1' => 'Langata Road',
                'city'          => 'Nairobi',
                'country'       => 'Kenya',
                'capacity'      => 5000,
                'county'        => 'Nairobi County',
                'created_by'    => 1,
            ],
            'sarit' => [
                'name'          => 'Sarit Expo Centre',
                'address_line1' => 'Westlands',
                'city'          => 'Nairobi',
                'county'        => 'Nairobi County',
                'country'       => 'Kenya',
                'capacity'      => 1500,
                'created_by'    => 1,
            ],
            'kiccc' => [
                'name'          => 'Kenyatta International Conference Centre',
                'address_line1' => 'City Square, CBD',
                'city'          => 'Nairobi',
                'county'        => 'Nairobi County',
                'country'       => 'Kenya',
                'capacity'      => 3000,
                'created_by'    => 1,
            ],
        ];

        $venues = [];
        foreach ($venueData as $key => $data) {
            $venues[$key] = Venue::firstOrCreate(['name' => $data['name']], $data);
        }

        return $venues;
    }
}

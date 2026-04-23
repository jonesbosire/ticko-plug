<?php

namespace App\Http\Controllers\Public;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Serve the sitemap index (splits into sub-sitemaps for large catalogs).
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap:index', now()->addDay(), fn () => $this->buildIndex());

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Static pages sitemap.
     */
    public function static(): Response
    {
        $xml = Cache::remember('sitemap:static', now()->addDay(), fn () => $this->buildStatic());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Events sitemap — one URL per published event.
     */
    public function events(): Response
    {
        $xml = Cache::remember('sitemap:events', now()->addHours(6), fn () => $this->buildEvents());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * Categories sitemap.
     */
    public function categories(): Response
    {
        $xml = Cache::remember('sitemap:categories', now()->addDay(), fn () => $this->buildCategories());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    // ──────────────────────────────────────────────────────────
    // Builders
    // ──────────────────────────────────────────────────────────

    private function buildIndex(): string
    {
        $sitemaps = [
            ['loc' => route('sitemap.static'),     'lastmod' => now()->toAtomString()],
            ['loc' => route('sitemap.events'),     'lastmod' => now()->toAtomString()],
            ['loc' => route('sitemap.categories'), 'lastmod' => now()->toAtomString()],
        ];

        $lines = ['<?xml version="1.0" encoding="UTF-8"?>',
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

        foreach ($sitemaps as $s) {
            $lines[] = '  <sitemap>';
            $lines[] = '    <loc>' . e($s['loc']) . '</loc>';
            $lines[] = '    <lastmod>' . $s['lastmod'] . '</lastmod>';
            $lines[] = '  </sitemap>';
        }

        $lines[] = '</sitemapindex>';

        return implode("\n", $lines);
    }

    private function buildStatic(): string
    {
        $pages = [
            ['url' => route('home'),           'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => route('events.index'),   'priority' => '0.9', 'changefreq' => 'hourly'],
            ['url' => route('search'),         'priority' => '0.7', 'changefreq' => 'weekly'],
        ];

        return $this->buildUrlset($pages);
    }

    private function buildEvents(): string
    {
        $events = Event::query()
            ->where('status', EventStatus::Published)
            ->where('visibility', 'public')
            ->where('start_datetime', '>', now()->subDays(30)) // keep past 30 days
            ->select(['slug', 'title', 'updated_at', 'start_datetime'])
            ->orderByDesc('updated_at')
            ->get();

        $pages = $events->map(fn ($event) => [
            'url'        => route('events.show', $event),
            'lastmod'    => $event->updated_at->toAtomString(),
            'priority'   => $event->start_datetime->isFuture() ? '0.9' : '0.5',
            'changefreq' => $event->start_datetime->isFuture() ? 'daily' : 'monthly',
        ])->toArray();

        return $this->buildUrlset($pages);
    }

    private function buildCategories(): string
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->select(['slug', 'updated_at'])
            ->get();

        $pages = $categories->map(fn ($cat) => [
            'url'        => route('events.category', $cat),
            'lastmod'    => $cat->updated_at->toAtomString(),
            'priority'   => '0.8',
            'changefreq' => 'daily',
        ])->toArray();

        return $this->buildUrlset($pages);
    }

    private function buildUrlset(array $pages): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"',
            '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'];

        foreach ($pages as $page) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . e($page['url']) . '</loc>';
            if (! empty($page['lastmod'])) {
                $lines[] = '    <lastmod>' . $page['lastmod'] . '</lastmod>';
            }
            if (! empty($page['changefreq'])) {
                $lines[] = '    <changefreq>' . $page['changefreq'] . '</changefreq>';
            }
            if (! empty($page['priority'])) {
                $lines[] = '    <priority>' . $page['priority'] . '</priority>';
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines);
    }
}

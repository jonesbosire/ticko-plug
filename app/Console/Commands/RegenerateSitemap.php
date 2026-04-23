<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegenerateSitemap extends Command
{
    protected $signature   = 'sitemap:regenerate {--ping : Ping Google and Bing after regeneration}';
    protected $description = 'Bust sitemap cache and optionally ping search engines';

    public function handle(): int
    {
        $keys = ['sitemap:index', 'sitemap:static', 'sitemap:events', 'sitemap:categories'];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        $this->info('Sitemap cache cleared. It will be regenerated on next request.');

        if ($this->option('ping')) {
            $sitemapUrl = urlencode(route('sitemap.index'));

            try {
                Http::timeout(5)->get("https://www.google.com/ping?sitemap={$sitemapUrl}");
                Http::timeout(5)->get("https://www.bing.com/ping?sitemap={$sitemapUrl}");
                $this->info('Search engines pinged.');
            } catch (\Throwable $e) {
                Log::warning('Sitemap ping failed', ['error' => $e->getMessage()]);
                $this->warn('Search engine ping failed (non-fatal): ' . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}

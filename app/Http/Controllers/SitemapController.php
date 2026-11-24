<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $baseUrl = rtrim(config('app.url') ?: url('/'), '/');
        $now = now()->toAtomString();

        $urls = [
            [
                'loc' => $baseUrl . '/',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => $baseUrl . '/lucky-numbers',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/recheck',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/accuracy',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => $baseUrl . '/vote/leaderboard',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => $baseUrl . '/statistics',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => $baseUrl . '/lotto-analyzer',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => $baseUrl . '/phone-analyzer',
                'lastmod' => $now,
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/dream-interpretation',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '0.8',
            ],
            [
                'loc' => $baseUrl . '/blog',
                'lastmod' => $now,
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
            [
                'loc' => $baseUrl . '/advertise',
                'lastmod' => $now,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ],
        ];

        // ดึงบทความที่เผยแพร่แล้วทั้งหมดเข้า Sitemap อัตโนมัติ
        $posts = Post::query()
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('published_at')
            ->get(['slug', 'updated_at', 'published_at']);

        foreach ($posts as $post) {
            if (!$post->slug) {
                continue;
            }
            $lastMod = ($post->updated_at ?? $post->published_at ?? now())->toAtomString();
            $urls[] = [
                'loc' => $baseUrl . '/blog/' . rawurlencode($post->slug),
                'lastmod' => $lastMod,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}

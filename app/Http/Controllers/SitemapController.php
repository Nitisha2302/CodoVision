<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $base = rtrim((string) config('seo.site_url', 'https://codovision.tech'), '/');
        $now = now()->toAtomString();

        $urls = [
            ['loc' => $base . '/', 'changefreq' => 'weekly', 'priority' => '1.0', 'lastmod' => $now],
            ['loc' => $base . '/about', 'changefreq' => 'monthly', 'priority' => '0.9', 'lastmod' => $now],
            ['loc' => $base . '/services', 'changefreq' => 'weekly', 'priority' => '0.9', 'lastmod' => $now],
            ['loc' => $base . '/projects', 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => $now],
            ['loc' => $base . '/contact', 'changefreq' => 'monthly', 'priority' => '0.8', 'lastmod' => $now],
            ['loc' => $base . '/process', 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => $now],
            ['loc' => $base . '/technologies', 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => $now],
            ['loc' => $base . '/packages', 'changefreq' => 'monthly', 'priority' => '0.6', 'lastmod' => $now],
            ['loc' => $base . '/design', 'changefreq' => 'monthly', 'priority' => '0.6', 'lastmod' => $now],
        ];

        foreach (array_values(config('portfolio.services', [])) as $service) {
            if (empty($service['slug'])) {
                continue;
            }
            $urls[] = [
                'loc' => $base . '/services/' . $service['slug'],
                'changefreq' => 'monthly',
                'priority' => '0.8',
                'lastmod' => $now,
            ];
        }

        foreach (array_values(config('portfolio.projects', [])) as $project) {
            if (empty($project['slug'])) {
                continue;
            }
            $urls[] = [
                'loc' => $base . '/projects/' . $project['slug'],
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => $now,
            ];
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}

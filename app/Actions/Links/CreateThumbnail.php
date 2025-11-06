<?php

namespace App\Actions\Links;

use App\Models\Link;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CreateThumbnail
{
    public function execute(Link $link): void
    {
        $thumbnail = $this->getThumbnail($link->url);

        $link->update([
            'thumbnail' => $thumbnail,
        ]);
    }

    /**
     * Attempt to create a thumbnail for the given URL.
     *
     * Strategy:
     * 1) If a screenshot service template is configured, request a screenshot for the URL.
     * 2) Fallback: Fetch the page and try to resolve an Open Graph image (og:image) to use as thumbnail.
     * 3) If all fails, return an empty string so UI can decide how to render a placeholder.
     */
    private function getThumbnail(string $url): string
    {
        $stored = $this->tryScreenshotService($url);
        if ($stored !== '') {
            return $stored;
        }

        $stored = $this->tryOpenGraphImage($url);
        if ($stored !== '') {
            return $stored;
        }

        return '';
    }

    private function tryScreenshotService(string $targetUrl): string
    {
        $template = (string) (config('services.link_thumbnail.screenshot_url_template') ?? '');
        if ($template === '') {
            return '';
        }

        $screenshotUrl = str_replace('{url}', urlencode($targetUrl), $template);

        try {
            $response = Http::withHeaders($this->defaultHeaders())
                ->timeout((int) (config('services.link_thumbnail.timeout', 10)))
                ->connectTimeout((int) (config('services.link_thumbnail.connect_timeout', 5)))
                ->get($screenshotUrl);

            if (! $response->successful()) {
                return '';
            }

            $mime = $response->header('Content-Type', 'image/jpeg');
            if (! str_starts_with($mime, 'image/')) {
                return '';
            }

            $extension = $this->extensionFromMime($mime);
            $path = $this->storePublicThumb($response->body(), $extension, $targetUrl);

            return $path;
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function tryOpenGraphImage(string $targetUrl): string
    {
        try {
            $htmlResponse = Http::withHeaders($this->defaultHeaders())
                ->timeout(8)
                ->connectTimeout(5)
                ->get($targetUrl);

            if (! $htmlResponse->successful()) {
                return '';
            }

            $html = $htmlResponse->body();
            $ogImage = $this->extractOgImage($html);
            if ($ogImage === null) {
                return '';
            }

            $imageUrl = $this->absolutizeUrl($ogImage, $targetUrl);

            $imgResponse = Http::withHeaders($this->defaultHeaders())
                ->timeout(15)
                ->connectTimeout(5)
                ->get($imageUrl);

            if (! $imgResponse->successful()) {
                return '';
            }

            $mime = $imgResponse->header('Content-Type', 'image/jpeg');
            if (! str_starts_with($mime, 'image/')) {
                return '';
            }

            $extension = $this->extensionFromMime($mime);
            $path = $this->storePublicThumb($imgResponse->body(), $extension, $targetUrl);

            return $path;
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function defaultHeaders(): array
    {
        $ua = (string) (config('services.link_thumbnail.user_agent')
            ?? 'Mozilla/5.0 (compatible; BookmarkAppBot/1.0; +https://example.test)');

        return [
            'User-Agent' => $ua,
            'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
        ];
    }

    private function storePublicThumb(string $binary, string $extension, string $sourceUrl): string
    {
        $hash = substr(sha1($sourceUrl.'|'.microtime(true)), 0, 16);
        $filename = $hash.'.'.$extension;
        $path = 'thumbnails/'.$filename;

        Storage::disk('public')->put($path, $binary);

        // return Storage::disk('public')->url($path);

        return $path;
    }

    private function extensionFromMime(string $mime): string
    {
        return match (true) {
            str_contains($mime, 'png') => 'png',
            str_contains($mime, 'webp') => 'webp',
            str_contains($mime, 'gif') => 'gif',
            default => 'jpg',
        };
    }

    private function extractOgImage(string $html): ?string
    {
        // Very small, regex-based scan for <meta property="og:image" content="...">
        if (preg_match('/<meta[^>]+property=[\"\']og:image[\"\'][^>]*>/i', $html, $metaTag)) {
            if (preg_match('/content=[\"\']([^\"\']+)[\"\']/', $metaTag[0], $contentMatch)) {
                return $contentMatch[1];
            }
        }

        // Twitter card as a fallback
        if (preg_match('/<meta[^>]+name=[\"\']twitter:image[\"\'][^>]*>/i', $html, $twTag)) {
            if (preg_match('/content=[\"\']([^\"\']+)[\"\']/', $twTag[0], $contentMatch)) {
                return $contentMatch[1];
            }
        }

        return null;
    }

    private function absolutizeUrl(string $possiblyRelative, string $baseUrl): string
    {
        // If absolute already
        if (str_starts_with($possiblyRelative, 'http://') || str_starts_with($possiblyRelative, 'https://')) {
            return $possiblyRelative;
        }

        $parts = parse_url($baseUrl) ?: [];
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $basePath = isset($parts['path']) ? rtrim(dirname($parts['path']), '/') : '';

        if (str_starts_with($possiblyRelative, '//')) {
            return $scheme.':'.$possiblyRelative;
        }

        if (str_starts_with($possiblyRelative, '/')) {
            return $scheme.'://'.$host.$port.$possiblyRelative;
        }

        return $scheme.'://'.$host.$port.$basePath.'/'.$possiblyRelative;
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

class NgrokUrlResolver
{
    private static bool $hasCachedValue = false;

    private static ?int $resolvedAt = null;

    private static ?string $resolvedUrl = null;

    public function publicRootUrl(): string
    {
        return $this->resolvePublicUrl()
            ?? $this->currentRequestBaseUrl()
            ?? rtrim((string) config('app.url'), '/');
    }

    public function resolvePublicUrl(): ?string
    {
        if (! (bool) config('services.ngrok.auto_detect', false)) {
            return null;
        }

        if (app()->runningUnitTests()) {
            return $this->fetchPublicUrl();
        }

        $ttl = max(1, (int) config('services.ngrok.cache_ttl', 15));

        if (self::$hasCachedValue && self::$resolvedAt !== null && (time() - self::$resolvedAt) < $ttl) {
            return self::$resolvedUrl;
        }

        self::$resolvedUrl = $this->fetchPublicUrl();
        self::$resolvedAt = time();
        self::$hasCachedValue = true;

        return self::$resolvedUrl;
    }

    public static function flushCache(): void
    {
        self::$hasCachedValue = false;
        self::$resolvedAt = null;
        self::$resolvedUrl = null;
    }

    private function fetchPublicUrl(): ?string
    {
        $apiUrl = (string) config('services.ngrok.api_url', 'http://127.0.0.1:4040/api/tunnels');

        if ($apiUrl === '') {
            return null;
        }

        try {
            $response = Http::timeout(1)->acceptJson()->get($apiUrl);

            if (! $response->successful()) {
                return null;
            }

            $tunnels = collect($response->json('tunnels', []))
                ->filter(fn ($tunnel) => filled($tunnel['public_url'] ?? null))
                ->values();

            if ($tunnels->isEmpty()) {
                return null;
            }

            $preferredScheme = strtolower((string) config('services.ngrok.preferred_scheme', 'https'));

            $preferredTunnel = $tunnels->first(
                fn ($tunnel) => strtolower((string) ($tunnel['proto'] ?? '')) === $preferredScheme
            );

            $publicUrl = $preferredTunnel['public_url'] ?? $tunnels->first()['public_url'] ?? null;

            return $this->normalizeUrl($publicUrl);
        } catch (Throwable) {
            return null;
        }
    }

    private function currentRequestBaseUrl(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();
        $host = strtolower((string) $request->getHost());

        if ($host === '' || in_array($host, ['127.0.0.1', '::1', 'localhost'], true)) {
            return null;
        }

        return $this->normalizeUrl($request->getSchemeAndHttpHost());
    }

    private function normalizeUrl(?string $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        $normalized = rtrim($url, '/');

        return filter_var($normalized, FILTER_VALIDATE_URL) ? $normalized : null;
    }
}

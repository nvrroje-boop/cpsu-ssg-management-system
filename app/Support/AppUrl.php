<?php

namespace App\Support;

class AppUrl
{
    public static function configUrl(): string
    {
        $configuredUrl = self::sanitize((string) env('APP_URL', ''));

        if ($configuredUrl !== '') {
            return rtrim($configuredUrl, '/');
        }

        $renderUrl = self::sanitize((string) env('RENDER_EXTERNAL_URL', ''));

        if ($renderUrl !== '') {
            return rtrim($renderUrl, '/');
        }

        return 'http://localhost';
    }

    public static function configHost(): string
    {
        return parse_url(self::configUrl(), PHP_URL_HOST) ?: 'localhost';
    }

    public static function baseUrl(): string
    {
        $resolver = app(NgrokUrlResolver::class);
        $baseUrl = rtrim($resolver->publicRootUrl(), '/');

        return $baseUrl !== '' ? $baseUrl : rtrim(url('/'), '/');
    }

    public static function route(string $name, array $parameters = []): string
    {
        $relativePath = route($name, $parameters, false);
        $baseUrl = self::baseUrl();

        return $baseUrl.'/'.ltrim($relativePath, '/');
    }

    public static function path(string $path = '/'): string
    {
        $baseUrl = self::baseUrl();
        $normalizedPath = '/'.ltrim($path, '/');

        return $baseUrl.$normalizedPath;
    }

    private static function sanitize(string $value): string
    {
        return trim(str_replace(["\r", "\n", "\t"], '', $value));
    }
}

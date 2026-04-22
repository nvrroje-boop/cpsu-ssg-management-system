<?php

namespace App\Support;

class AppUrl
{
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
}

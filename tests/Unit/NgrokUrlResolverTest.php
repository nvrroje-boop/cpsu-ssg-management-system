<?php

namespace Tests\Unit;

use App\Support\AppUrl;
use App\Support\NgrokUrlResolver;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NgrokUrlResolverTest extends TestCase
{
    protected function tearDown(): void
    {
        NgrokUrlResolver::flushCache();

        parent::tearDown();
    }

    public function test_it_prefers_the_live_https_ngrok_tunnel_for_generated_links(): void
    {
        config()->set('app.url', 'http://localhost:8000');
        config()->set('services.ngrok', [
            'auto_detect' => true,
            'api_url' => 'http://127.0.0.1:4040/api/tunnels',
            'preferred_scheme' => 'https',
            'cache_ttl' => 60,
        ]);

        Http::fake([
            'http://127.0.0.1:4040/api/tunnels' => Http::response([
                'tunnels' => [
                    ['proto' => 'http', 'public_url' => 'http://demo.ngrok-free.dev'],
                    ['proto' => 'https', 'public_url' => 'https://demo.ngrok-free.dev'],
                ],
            ], 200),
        ]);

        $resolver = app(NgrokUrlResolver::class);

        $this->assertSame('https://demo.ngrok-free.dev', $resolver->publicRootUrl());
        $this->assertSame('https://demo.ngrok-free.dev/login', AppUrl::route('login'));
        $this->assertSame('https://demo.ngrok-free.dev/', AppUrl::path('/'));

        Http::assertSentCount(3);
    }

    public function test_it_falls_back_to_app_url_when_ngrok_api_is_unavailable(): void
    {
        config()->set('app.url', 'http://localhost:8000');
        config()->set('services.ngrok', [
            'auto_detect' => true,
            'api_url' => 'http://127.0.0.1:4040/api/tunnels',
            'preferred_scheme' => 'https',
            'cache_ttl' => 60,
        ]);

        Http::fake([
            'http://127.0.0.1:4040/api/tunnels' => Http::response([], 500),
        ]);

        $resolver = app(NgrokUrlResolver::class);

        $this->assertSame('http://localhost:8000', $resolver->publicRootUrl());
        $this->assertSame('http://localhost:8000/login', AppUrl::route('login'));
    }
}

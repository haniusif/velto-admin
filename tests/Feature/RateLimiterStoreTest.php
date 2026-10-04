<?php

namespace Tests\Feature;

use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Throttle counters used to live in the database cache table, where a burst
 * of requests from one customer deadlocked MySQL on the same row and failed
 * the request with a 500 (production, 2026-09-28). They now live in files.
 */
class RateLimiterStoreTest extends TestCase
{
    use RefreshDatabase;

    private function productionLimiter(): RateLimiter
    {
        // Production: general cache on the database, throttling on files.
        config(['cache.default' => 'database', 'cache.limiter' => 'file']);
        $this->app->forgetInstance(RateLimiter::class);

        return $this->app->make(RateLimiter::class);
    }

    public function test_the_shipped_default_keeps_throttling_off_the_database(): void
    {
        // Tests override CACHE_LIMITER to array, so read the shipped default
        // from the config source rather than the running config.
        $this->assertStringContainsString(
            "'limiter' => env('CACHE_LIMITER', 'file')",
            file_get_contents(config_path('cache.php')),
        );
    }

    public function test_a_throttle_hit_never_writes_the_cache_table(): void
    {
        $limiter = $this->productionLimiter();
        $key = 'test:'.uniqid();

        $limiter->hit($key, 60);
        $limiter->hit($key, 60);

        $this->assertSame(0, DB::table('cache')->count(), 'throttling is back on the database cache table');
        $this->assertSame(2, $limiter->attempts($key));

        $limiter->clear($key);
    }

    public function test_it_still_limits(): void
    {
        $limiter = $this->productionLimiter();
        $key = 'test:'.uniqid();

        foreach (range(1, 3) as $_) {
            $limiter->hit($key, 60);
        }

        $this->assertTrue($limiter->tooManyAttempts($key, 3));
        $this->assertFalse($limiter->tooManyAttempts($key, 4));

        $limiter->clear($key);
    }
}

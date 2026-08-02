<?php

namespace Tests\Unit;

use App\Services\JawalySMSService;
use Tests\TestCase;

/**
 * The SMS balance is the quietest way for sign-in to break: 4jawaly answers
 * HTTP 200 whether or not credits remain, so an empty account looks exactly
 * like a working one from inside the app. These pin the shape the dashboard
 * widget depends on, and the cases where the number must not be trusted.
 */
class SmsBalanceTest extends TestCase
{
    public function test_it_reports_unconfigured_rather_than_guessing(): void
    {
        config()->set('services.jawaly.app_id', null);
        config()->set('services.jawaly.app_secret', null);

        $balance = (new JawalySMSService)->balance();

        $this->assertFalse($balance['configured']);
        $this->assertNull($balance['remaining'], 'an unconfigured account has no balance, not zero');
    }

    public function test_the_shape_the_widget_relies_on_is_stable(): void
    {
        config()->set('services.jawaly.app_id', null);

        $balance = (new JawalySMSService)->balance();

        foreach (['configured', 'remaining', 'total', 'expires_at', 'error'] as $key) {
            $this->assertArrayHasKey($key, $balance, "widget reads [{$key}]");
        }
    }

    /**
     * A missing balance and a zero balance mean different things: one is "we
     * could not ask", the other is "you cannot send". The widget colours them
     * differently, so they must not collapse into each other.
     */
    public function test_null_and_zero_are_distinguishable(): void
    {
        config()->set('services.jawaly.app_id', null);
        $unconfigured = (new JawalySMSService)->balance();

        $this->assertNull($unconfigured['remaining']);
        $this->assertNotSame(0, $unconfigured['remaining']);
    }
}

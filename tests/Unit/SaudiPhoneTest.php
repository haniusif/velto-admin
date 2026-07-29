<?php

namespace Tests\Unit;

use App\Support\SaudiPhone;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every form a Saudi customer might type has to reach one canonical number,
 * and anything that is not a Saudi mobile has to be refused rather than coerced.
 *
 * The old normaliser ended with `return '+'.$digits`, so "512345678" — exactly
 * what the apps send — became "+512345678". request-otp then answered 200 for a
 * number that could never receive an SMS.
 */
class SaudiPhoneTest extends TestCase
{
    public static function acceptedForms(): array
    {
        return [
            'national, as the apps send it' => ['512345678'],
            'local form with trunk prefix' => ['0512345678'],
            'E.164' => ['+966512345678'],
            'E.164 without the plus' => ['966512345678'],
            'international access code' => ['00966512345678'],
            'dial code then trunk prefix' => ['9660512345678'],
            'spaced out, as pasted' => ['+966 51 234 5678'],
            'dashed' => ['051-234-5678'],
        ];
    }

    #[DataProvider('acceptedForms')]
    public function test_every_accepted_form_lands_on_the_same_number(string $input): void
    {
        $this->assertSame('+966512345678', SaudiPhone::normalize($input));
    }

    public function test_the_bare_national_number_is_not_left_without_a_dial_code(): void
    {
        // The regression: this used to return '+512345678'.
        $this->assertSame('+966512345678', SaudiPhone::normalize('512345678'));
    }

    public static function rejectedForms(): array
    {
        return [
            'empty' => [''],
            'null' => [null],
            'too short' => ['51234567'],
            'too long' => ['5123456789'],
            'a landline, not a mobile' => ['0112345678'],
            'does not start with 5' => ['412345678'],
            'another country' => ['+201012345678'],
            'letters only' => ['not a phone'],
            'dial code alone' => ['966'],
            'zeros only' => ['000000000'],
        ];
    }

    #[DataProvider('rejectedForms')]
    public function test_anything_that_is_not_a_saudi_mobile_is_refused(?string $input): void
    {
        $this->assertNull(SaudiPhone::normalize($input));
        $this->assertFalse(SaudiPhone::isValid($input));
    }

    public function test_normalizing_is_idempotent(): void
    {
        $once = SaudiPhone::normalize('0512345678');
        $this->assertSame($once, SaudiPhone::normalize($once));
    }

    public function test_the_demo_number_used_by_app_review_normalizes(): void
    {
        foreach (['535097129', '0535097129', '+966535097129', '966535097129'] as $form) {
            $this->assertSame('+966535097129', SaudiPhone::normalize($form), $form);
        }
    }
}

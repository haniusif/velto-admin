<?php

namespace App\Models\Concerns;

use App\Support\SaudiPhone;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Stores `phone` in canonical E.164 no matter how it was typed.
 *
 * Sign-in looks the number up by exact match against the normalised form the
 * apps send (+9665XXXXXXXX). Anything saved in another shape is unreachable:
 * the person requests a code, gets one, and is told no account exists.
 *
 * That is not hypothetical — worker 7 was created through the admin panel as
 * "537938734" and could never sign in. The API path already normalised on the
 * way in; every other path (Filament forms, seeders, tinker, imports) did not.
 * Doing it in the model covers all of them.
 *
 * A value that is not a Saudi mobile is stored unchanged rather than discarded,
 * so a bad import is visible in the panel instead of silently blanked.
 */
trait NormalisesPhone
{
    protected function phone(): Attribute
    {
        return Attribute::set(
            fn (?string $value): ?string => $value === null || $value === ''
                ? $value
                : (SaudiPhone::normalize($value) ?? $value),
        );
    }
}

<?php

namespace App\Rules;

use App\Support\SaudiPhone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects anything that is not a Saudi mobile number.
 *
 * Without this the endpoint answered 200 for input it could not actually send a
 * code to, because normalisation used to coerce any digits into a "+" prefixed
 * string. Failing at validation gives the caller a 422 naming the field, which
 * is what the apps already know how to display.
 */
class SaudiMobile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! SaudiPhone::isValid(is_string($value) ? $value : null)) {
            $fail('Enter a Saudi mobile number, for example 0512345678.');
        }
    }
}

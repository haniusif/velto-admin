<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Free text a person typed: no markup, no control characters.
 *
 * Everything the API stores is rendered by our own clients, which escape on
 * output — this rule is the second line: a value that contains tags or
 * non-printing characters was not typed by a customer in a text box.
 */
class PlainText implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if ($value !== strip_tags($value)) {
            $fail('The :attribute may not contain HTML.');

            return;
        }

        // Allow newlines and tabs in multi-line fields; reject the rest of C0/C1.
        if (preg_match('/[^\P{C}\n\r\t]/u', $value)) {
            $fail('The :attribute contains invalid characters.');
        }
    }
}

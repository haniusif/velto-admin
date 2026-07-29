<?php

namespace App\Support;

/**
 * Canonicalises Saudi mobile numbers to E.164 (+9665XXXXXXXX).
 *
 * Velto operates only in Saudi Arabia, so a number that is not a Saudi mobile
 * is not a number we can send a code to. [normalize] therefore returns null
 * rather than guessing — the previous behaviour prepended a bare "+" to
 * whatever digits arrived, so "512345678" became "+512345678" and the API
 * answered 200 for a number that could never receive an SMS.
 *
 * Accepted inputs, all landing on +966512345678:
 *
 *   512345678        national, as the apps send it
 *   0512345678       the local form, with the trunk prefix
 *   +966512345678    E.164
 *   966512345678     E.164 without the plus
 *   00966512345678   international access code
 *   9660512345678    dial code followed by the trunk prefix
 *
 * Anything else — wrong length, a landline, a non-Saudi country — is null.
 */
final class SaudiPhone
{
    /** Saudi mobile numbers are nine digits and always start with 5. */
    private const NATIONAL = '/^5\d{8}$/';

    public const DIAL_CODE = '966';

    public static function normalize(?string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $input) ?? '';
        if ($digits === '') {
            return null;
        }

        // 00 is the international access code; drop it before looking for 966.
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, self::DIAL_CODE)) {
            $digits = substr($digits, strlen(self::DIAL_CODE));
        }

        // A trunk prefix can survive either branch: "0512…" or "9660512…".
        $national = ltrim($digits, '0');

        return preg_match(self::NATIONAL, $national) === 1
            ? '+'.self::DIAL_CODE.$national
            : null;
    }

    /** True when [$input] is a Saudi mobile number in any accepted form. */
    public static function isValid(?string $input): bool
    {
        return self::normalize($input) !== null;
    }
}

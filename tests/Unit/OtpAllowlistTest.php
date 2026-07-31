<?php

namespace Tests\Unit;

use App\Support\SaudiPhone;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The allowlist is compared against the canonical +9665XXXXXXXX form that
 * normalisation produces. Writing a number in .env any other way — 0535097129,
 * or a bare 535097129 — used to miss silently: no error, the number just fell
 * through to a real SMS and the fixed code never applied.
 *
 * Config normalises each entry now, so these check the shapes someone actually
 * types into an .env file all land on the same value.
 */
class OtpAllowlistTest extends TestCase
{
    private function allowlistFrom(string $env): array
    {
        return array_values(array_filter(array_map(
            static fn (string $phone): ?string => SaudiPhone::normalize($phone),
            explode(',', $env),
        )));
    }

    #[DataProvider('envSpellings')]
    public function test_a_number_matches_however_it_is_written(string $env): void
    {
        $this->assertSame(['+966535097129'], $this->allowlistFrom($env));
    }

    public static function envSpellings(): array
    {
        return [
            'bare national' => ['535097129'],
            'local form' => ['0535097129'],
            'with dial code' => ['966535097129'],
            'E.164' => ['+966535097129'],
            'padded with spaces' => [' +966535097129 '],
        ];
    }

    public function test_the_three_allowlisted_numbers_resolve(): void
    {
        $allowlist = $this->allowlistFrom('966535097129,966569695852,966542789386');

        $this->assertSame(
            ['+966535097129', '+966569695852', '+966542789386'],
            $allowlist,
        );
    }

    public function test_junk_entries_are_dropped_rather_than_matching_nothing(): void
    {
        // A typo should not become an entry that can never match; it should not
        // become an entry at all.
        $allowlist = $this->allowlistFrom('966535097129,,not-a-number,0112345678');

        $this->assertSame(['+966535097129'], $allowlist);
    }

    public function test_an_empty_setting_allowlists_nobody(): void
    {
        $this->assertSame([], $this->allowlistFrom(''));
    }
}

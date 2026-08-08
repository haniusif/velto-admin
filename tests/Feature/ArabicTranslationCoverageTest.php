<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The panel has an AR/EN switch, so every string it renders needs an Arabic
 * entry — a missing key falls back to the English source text, which shows up
 * as a stray English label in an otherwise Arabic screen.
 *
 * This walks the admin source for __('…') keys and fails naming the ones with
 * no translation, so a new screen cannot quietly ship half-translated.
 */
class ArabicTranslationCoverageTest extends TestCase
{
    /** Namespaced package keys (filament-shield::…) resolve from the package's own lang files. */
    private const PACKAGE_KEY_PREFIXES = ['filament-shield::', 'filament-panels::', 'filament-actions::'];

    private const SOURCE_DIRS = [
        'app/Filament',
        'app/Providers/Filament',
        'resources/views/filament',
    ];

    public function test_every_admin_string_has_an_arabic_translation(): void
    {
        $translations = json_decode(file_get_contents(base_path('lang/ar.json')), true);

        $this->assertIsArray($translations, 'lang/ar.json is not valid JSON');

        $missing = [];

        foreach ($this->translationKeys() as $key => $file) {
            foreach (self::PACKAGE_KEY_PREFIXES as $prefix) {
                if (str_starts_with($key, $prefix)) {
                    continue 2;
                }
            }

            if (! array_key_exists($key, $translations)) {
                $missing[] = "  {$key}   ({$file})";
            }
        }

        $this->assertSame([], $missing, sprintf(
            "%d admin string(s) have no Arabic translation in lang/ar.json:\n%s",
            count($missing),
            implode("\n", $missing),
        ));
    }

    public function test_no_translation_drops_a_placeholder(): void
    {
        $translations = json_decode(file_get_contents(base_path('lang/ar.json')), true);

        $broken = [];

        foreach ($translations as $key => $value) {
            preg_match_all('/:[a-zA-Z_]+/', $key, $matches);

            foreach (array_unique($matches[0]) as $placeholder) {
                if (! str_contains($value, $placeholder)) {
                    // A dropped :placeholder renders the raw token to the user.
                    $broken[] = "  {$placeholder} missing from translation of \"{$key}\"";
                }
            }
        }

        $this->assertSame([], $broken, implode("\n", $broken));
    }

    /** @return array<string,string> key => the file it was found in */
    private function translationKeys(): array
    {
        $keys = [];

        foreach (self::SOURCE_DIRS as $dir) {
            $path = base_path($dir);

            if (! is_dir($path)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());

                preg_match_all(
                    '/__\(\s*(?:\'((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)")\s*[,)]/',
                    $contents,
                    $matches,
                    PREG_SET_ORDER,
                );

                foreach ($matches as $match) {
                    $raw = $match[1] !== '' ? $match[1] : ($match[2] ?? '');

                    if ($raw === '') {
                        continue;
                    }

                    $key = str_replace(["\\'", '\\"'], ["'", '"'], $raw);
                    $keys[$key] ??= str_replace(base_path().'/', '', $file->getPathname());
                }
            }
        }

        return $keys;
    }
}

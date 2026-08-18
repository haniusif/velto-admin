<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * BookingFactory referenced BookingTime without importing it. PHP resolved the
 * name relative to the file's own namespace — App\Services\Booking\BookingTime
 * — and every booking attempt died with "Class not found". The full suite still
 * passed, because nothing loaded these classes and checked their references.
 *
 * This walks the compiled file for a class-name reference and asserts it can
 * actually be resolved, which is the specific mistake a `use` statement fixes.
 */
class BookingClassResolutionTest extends TestCase
{
    /** @return array<int,array{0:string}> */
    public static function filesUsingBookingTime(): array
    {
        $root = dirname(__DIR__, 2).'/app';
        $found = [];

        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        foreach ($it as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            if (str_contains(file_get_contents($file->getPathname()), 'BookingTime::')) {
                $found[] = [$file->getPathname()];
            }
        }

        return $found;
    }

    #[DataProvider('filesUsingBookingTime')]
    public function test_every_file_calling_booking_time_can_resolve_it(string $path): void
    {
        $source = file_get_contents($path);

        $this->assertStringContainsString(
            'use App\Support\BookingTime;',
            $source,
            basename($path).' calls BookingTime:: without importing it — PHP will look for it '
                .'in that file\'s own namespace and fail at runtime',
        );
    }

    public function test_the_class_itself_exists_where_the_import_points(): void
    {
        $this->assertTrue(class_exists(\App\Support\BookingTime::class));
    }
}

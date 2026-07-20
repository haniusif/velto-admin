<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Riyadh neighbourhood boundaries imported from Google My Maps KML
 * (database/data/riyadh_districts.json — 189 districts).
 *
 * Coverage defaults to Al Mahdiyah + Irqah to preserve current behaviour;
 * admins toggle the rest from the Coverage Map page.
 */
class DistrictSeeder extends Seeder
{
    /** Districts covered by default (matched against the KML Arabic name). */
    private const DEFAULT_COVERED = ['حي المهدية', 'عرقة'];

    public function run(): void
    {
        $path = database_path('data/riyadh_districts.json');
        if (! is_file($path)) {
            $this->command?->warn("DistrictSeeder: {$path} not found — skipping.");

            return;
        }

        $districts = json_decode(file_get_contents($path), true) ?? [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('districts')->truncate();

        $now = now();
        $rows = [];
        foreach ($districts as $d) {
            $name = trim($d['name'] ?? '');
            if ($name === '' || empty($d['geometry'])) {
                continue;
            }
            $rows[] = [
                'city' => 'Riyadh',
                'name' => $name,
                'name_ar' => $name,
                'slug' => Str::slug($name) ?: null,
                'geometry' => json_encode($d['geometry'], JSON_UNESCAPED_UNICODE),
                'is_covered' => in_array($name, self::DEFAULT_COVERED, true),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 50) as $chunk) {
            DB::table('districts')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command?->info('Seeded '.count($rows).' Riyadh districts.');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `zones` (2 rows).
 * Regenerated from the live `velto_admin` database.
 */
class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('zones')->truncate();

        $rows = [
            [
                'id' => 1,
                'area_id' => 1,
                'name' => 'Olaya Core',
                'name_ar' => 'قلب العليا',
                'color' => '#8863E5',
                'geometry' => '{"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[46.673, 24.6784], [46.697, 24.6784], [46.697, 24.7024], [46.673, 24.7024], [46.673, 24.6784]]]}, "properties": {}}',
                'is_active' => 0,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:31:05',
            ],
            [
                'id' => 2,
                'area_id' => 2,
                'name' => 'Al Malqa North',
                'name_ar' => 'الملقا الشمالية',
                'color' => '#B38BEE',
                'geometry' => '{"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[46.618, 24.8], [46.642, 24.8], [46.642, 24.824], [46.618, 24.824], [46.618, 24.8]]]}, "properties": {}}',
                'is_active' => 0,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:31:05',
            ],
            [
                'id' => 3,
                'area_id' => 14,
                'name' => 'Al Mahdiyah',
                'name_ar' => 'المهدية',
                'color' => '#8863E5',
                'geometry' => '{"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[46.616, 24.62], [46.64, 24.62], [46.64, 24.644], [46.616, 24.644], [46.616, 24.62]]]}, "properties": {}}',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:31:05',
            ],
            [
                'id' => 4,
                'area_id' => 15,
                'name' => 'Irqah',
                'name_ar' => 'عرقة',
                'color' => '#B38BEE',
                'geometry' => '{"type": "Feature", "geometry": {"type": "Polygon", "coordinates": [[[46.548, 24.698], [46.572, 24.698], [46.572, 24.722], [46.548, 24.722], [46.548, 24.698]]]}, "properties": {}}',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:31:05',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('zones')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

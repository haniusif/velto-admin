<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `vehicle_colors` (13 rows).
 * Regenerated from the live `velto_admin` database.
 */
class VehicleColorSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('vehicle_colors')->truncate();

        $rows = [
            [
                'id' => 1,
                'slug' => 'white',
                'name' => 'White',
                'name_ar' => 'أبيض',
                'hex' => '#FFFFFF',
                'is_light_swatch' => 1,
                'sort_order' => 0,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 2,
                'slug' => 'black',
                'name' => 'Black',
                'name_ar' => 'أسود',
                'hex' => '#0A0A0A',
                'is_light_swatch' => 0,
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 3,
                'slug' => 'silver',
                'name' => 'Silver',
                'name_ar' => 'فضي',
                'hex' => '#C0C0C0',
                'is_light_swatch' => 0,
                'sort_order' => 2,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 4,
                'slug' => 'gray',
                'name' => 'Gray',
                'name_ar' => 'رمادي',
                'hex' => '#6E6E73',
                'is_light_swatch' => 0,
                'sort_order' => 3,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 5,
                'slug' => 'red',
                'name' => 'Red',
                'name_ar' => 'أحمر',
                'hex' => '#D7484F',
                'is_light_swatch' => 0,
                'sort_order' => 4,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 6,
                'slug' => 'blue',
                'name' => 'Blue',
                'name_ar' => 'أزرق',
                'hex' => '#1B6EE2',
                'is_light_swatch' => 0,
                'sort_order' => 5,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 7,
                'slug' => 'navy',
                'name' => 'Navy',
                'name_ar' => 'كحلي',
                'hex' => '#002C5F',
                'is_light_swatch' => 0,
                'sort_order' => 6,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 8,
                'slug' => 'green',
                'name' => 'Green',
                'name_ar' => 'أخضر',
                'hex' => '#2E7D4F',
                'is_light_swatch' => 0,
                'sort_order' => 7,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 9,
                'slug' => 'gold',
                'name' => 'Gold',
                'name_ar' => 'ذهبي',
                'hex' => '#C9A961',
                'is_light_swatch' => 0,
                'sort_order' => 8,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 10,
                'slug' => 'beige',
                'name' => 'Beige',
                'name_ar' => 'بيج',
                'hex' => '#D9C8A4',
                'is_light_swatch' => 1,
                'sort_order' => 9,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 11,
                'slug' => 'brown',
                'name' => 'Brown',
                'name_ar' => 'بني',
                'hex' => '#6B4E2E',
                'is_light_swatch' => 0,
                'sort_order' => 10,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 12,
                'slug' => 'orange',
                'name' => 'Orange',
                'name_ar' => 'برتقالي',
                'hex' => '#E6822E',
                'is_light_swatch' => 0,
                'sort_order' => 11,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 13,
                'slug' => 'yellow',
                'name' => 'Yellow',
                'name_ar' => 'أصفر',
                'hex' => '#E6C42E',
                'is_light_swatch' => 0,
                'sort_order' => 12,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('vehicle_colors')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

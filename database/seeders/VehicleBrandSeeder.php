<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `vehicle_brands` (6 rows).
 * Regenerated from the live `velto_admin` database.
 */
class VehicleBrandSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('vehicle_brands')->truncate();

        $rows = [
            [
                'id' => 1,
                'slug' => 'toyota',
                'name' => 'Toyota',
                'name_ar' => 'تويوتا',
                'icon_path' => 'brands/toyota.svg',
                'sort_order' => 0,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 2,
                'slug' => 'lexus',
                'name' => 'Lexus',
                'name_ar' => 'لكزس',
                'icon_path' => 'brands/lexus.svg',
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 3,
                'slug' => 'hyundai',
                'name' => 'Hyundai',
                'name_ar' => 'هيونداي',
                'icon_path' => 'brands/hyundai.svg',
                'sort_order' => 2,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 4,
                'slug' => 'nissan',
                'name' => 'Nissan',
                'name_ar' => 'نيسان',
                'icon_path' => 'brands/nissan.svg',
                'sort_order' => 3,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 5,
                'slug' => 'mercedes_benz',
                'name' => 'Mercedes-Benz',
                'name_ar' => 'مرسيدس-بنز',
                'icon_path' => 'brands/mercedes_benz.svg',
                'sort_order' => 4,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
            [
                'id' => 6,
                'slug' => 'bmw',
                'name' => 'BMW',
                'name_ar' => 'بي إم دبليو',
                'icon_path' => null,
                'sort_order' => 5,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:56',
                'updated_at' => '2026-05-10 19:19:56',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('vehicle_brands')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

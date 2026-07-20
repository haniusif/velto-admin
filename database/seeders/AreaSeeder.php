<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `areas` (13 rows).
 * Regenerated from the live `velto_admin` database.
 */
class AreaSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('areas')->truncate();

        $rows = [
            [
                'id' => 1,
                'city_id' => 1,
                'name' => 'Olaya',
                'name_ar' => 'العليا',
                'latitude' => '24.6904000',
                'longitude' => '46.6850000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 2,
                'city_id' => 1,
                'name' => 'Al Malqa',
                'name_ar' => 'الملقا',
                'latitude' => '24.8045000',
                'longitude' => '46.6293000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 3,
                'city_id' => 1,
                'name' => 'Al Yasmin',
                'name_ar' => 'الياسمين',
                'latitude' => '24.8312000',
                'longitude' => '46.6398000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 4,
                'city_id' => 1,
                'name' => 'Hittin',
                'name_ar' => 'حطين',
                'latitude' => '24.7691000',
                'longitude' => '46.6177000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 5,
                'city_id' => 1,
                'name' => 'Diplomatic Quarter',
                'name_ar' => 'الحي الدبلوماسي',
                'latitude' => '24.6690000',
                'longitude' => '46.6224000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 6,
                'city_id' => 1,
                'name' => 'Al Nakheel',
                'name_ar' => 'النخيل',
                'latitude' => '24.7619000',
                'longitude' => '46.6394000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 7,
                'city_id' => 1,
                'name' => 'Al Sahafa',
                'name_ar' => 'الصحافة',
                'latitude' => '24.8011000',
                'longitude' => '46.6491000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 8,
                'city_id' => 1,
                'name' => 'Al Murabba',
                'name_ar' => 'المربع',
                'latitude' => '24.6589000',
                'longitude' => '46.7174000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 9,
                'city_id' => 2,
                'name' => 'Al Salamah',
                'name_ar' => 'السلامة',
                'latitude' => '21.5945000',
                'longitude' => '39.1707000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 10,
                'city_id' => 2,
                'name' => 'Al Hamra',
                'name_ar' => 'الحمراء',
                'latitude' => '21.5128000',
                'longitude' => '39.1764000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 11,
                'city_id' => 2,
                'name' => 'Ar Rawdah',
                'name_ar' => 'الروضة',
                'latitude' => '21.5666000',
                'longitude' => '39.1701000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 12,
                'city_id' => 5,
                'name' => 'Al Faisaliyah',
                'name_ar' => 'الفيصلية',
                'latitude' => '26.4101000',
                'longitude' => '50.0893000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 13,
                'city_id' => 5,
                'name' => 'Al Shati',
                'name_ar' => 'الشاطئ',
                'latitude' => '26.4499000',
                'longitude' => '50.1163000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 14,
                'city_id' => 1,
                'name' => 'Al Mahdiyah',
                'name_ar' => 'المهدية',
                'latitude' => '24.6320000',
                'longitude' => '46.6280000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 15,
                'city_id' => 1,
                'name' => 'Irqah',
                'name_ar' => 'عرقة',
                'latitude' => '24.7100000',
                'longitude' => '46.5600000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('areas')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `cities` (10 rows).
 * Regenerated from the live `velto_admin` database.
 */
class CitySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('cities')->truncate();

        $rows = [
            [
                'id' => 1,
                'name' => 'Riyadh',
                'name_ar' => 'الرياض',
                'slug' => 'riyadh',
                'country' => 'SA',
                'latitude' => '24.7136000',
                'longitude' => '46.6753000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 2,
                'name' => 'Jeddah',
                'name_ar' => 'جدة',
                'slug' => 'jeddah',
                'country' => 'SA',
                'latitude' => '21.4858000',
                'longitude' => '39.1925000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 3,
                'name' => 'Mecca',
                'name_ar' => 'مكة',
                'slug' => 'mecca',
                'country' => 'SA',
                'latitude' => '21.3891000',
                'longitude' => '39.8579000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 4,
                'name' => 'Medina',
                'name_ar' => 'المدينة',
                'slug' => 'medina',
                'country' => 'SA',
                'latitude' => '24.5247000',
                'longitude' => '39.5692000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 5,
                'name' => 'Dammam',
                'name_ar' => 'الدمام',
                'slug' => 'dammam',
                'country' => 'SA',
                'latitude' => '26.4207000',
                'longitude' => '50.0888000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 6,
                'name' => 'Khobar',
                'name_ar' => 'الخبر',
                'slug' => 'khobar',
                'country' => 'SA',
                'latitude' => '26.2794000',
                'longitude' => '50.2083000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 7,
                'name' => 'Dhahran',
                'name_ar' => 'الظهران',
                'slug' => 'dhahran',
                'country' => 'SA',
                'latitude' => '26.2361000',
                'longitude' => '50.0393000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 8,
                'name' => 'Tabuk',
                'name_ar' => 'تبوك',
                'slug' => 'tabuk',
                'country' => 'SA',
                'latitude' => '28.3998000',
                'longitude' => '36.5700000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 9,
                'name' => 'Abha',
                'name_ar' => 'أبها',
                'slug' => 'abha',
                'country' => 'SA',
                'latitude' => '18.2164000',
                'longitude' => '42.5053000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 10,
                'name' => 'Taif',
                'name_ar' => 'الطائف',
                'slug' => 'taif',
                'country' => 'SA',
                'latitude' => '21.4373000',
                'longitude' => '40.5127000',
                'is_active' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('cities')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

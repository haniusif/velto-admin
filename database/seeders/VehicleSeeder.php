<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `vehicles` (11 rows).
 * Regenerated from the live `velto_admin` database.
 */
class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('vehicles')->truncate();

        $rows = [
            [
                'id' => 1,
                'customer_id' => 1,
                'name' => 'Daily',
                'brand' => 'Toyota',
                'model' => 'Camry',
                'color' => 'White',
                'plate' => 'ABC 1234',
                'photo_url' => null,
                'is_default' => 1,
                'created_at' => '2026-05-10 18:46:59',
                'updated_at' => '2026-05-10 18:46:59',
            ],
            [
                'id' => 2,
                'customer_id' => 2,
                'name' => null,
                'brand' => 'Lexus',
                'model' => 'ES350',
                'color' => 'Silver',
                'plate' => 'XYZ 9876',
                'photo_url' => null,
                'is_default' => 1,
                'created_at' => '2026-05-10 18:46:59',
                'updated_at' => '2026-05-10 18:46:59',
            ],
            [
                'id' => 3,
                'customer_id' => 3,
                'name' => 'Family',
                'brand' => 'Hyundai',
                'model' => 'Sonata',
                'color' => 'Black',
                'plate' => 'KSA 4567',
                'photo_url' => null,
                'is_default' => 1,
                'created_at' => '2026-05-10 18:46:59',
                'updated_at' => '2026-05-10 18:46:59',
            ],
            [
                'id' => 4,
                'customer_id' => 4,
                'name' => null,
                'brand' => 'Nissan',
                'model' => 'Patrol',
                'color' => 'Beige',
                'plate' => 'NSR 8521',
                'photo_url' => null,
                'is_default' => 1,
                'created_at' => '2026-05-10 18:46:59',
                'updated_at' => '2026-05-10 18:46:59',
            ],
            [
                'id' => 5,
                'customer_id' => 5,
                'name' => null,
                'brand' => 'BMW',
                'model' => 'X5',
                'color' => 'Black',
                'plate' => 'BMW 7799',
                'photo_url' => null,
                'is_default' => 1,
                'created_at' => '2026-05-10 18:46:59',
                'updated_at' => '2026-05-10 18:46:59',
            ],
            [
                'id' => 6,
                'customer_id' => 12,
                'name' => null,
                'brand' => 'Toyota',
                'model' => 'Camry',
                'color' => 'White',
                'plate' => 'ABC 1234',
                'photo_url' => null,
                'is_default' => 1,
                'created_at' => '2026-06-09 07:57:08',
                'updated_at' => '2026-06-09 07:57:08',
            ],
            [
                'id' => 7,
                'customer_id' => 13,
                'name' => null,
                'brand' => 'Lexus',
                'model' => 'RX',
                'color' => null,
                'plate' => 'XYZ 9',
                'photo_url' => null,
                'is_default' => 1,
                'created_at' => '2026-06-09 07:58:11',
                'updated_at' => '2026-06-09 07:58:11',
            ],
            [
                'id' => 8,
                'customer_id' => 14,
                'name' => null,
                'brand' => 'Kia',
                'model' => 'K5',
                'color' => null,
                'plate' => 'AAA 1',
                'photo_url' => null,
                'is_default' => 1,
                'created_at' => '2026-06-09 08:44:11',
                'updated_at' => '2026-06-09 08:44:11',
            ],
            [
                'id' => 9,
                'customer_id' => 15,
                'name' => null,
                'brand' => 'Toyota',
                'model' => 'Camry',
                'color' => 'White',
                'plate' => 'RYD 4521',
                'photo_url' => null,
                'is_default' => 1,
                'created_at' => '2026-06-09 15:45:01',
                'updated_at' => '2026-06-09 15:45:01',
            ],
            [
                'id' => 10,
                'customer_id' => 10,
                'name' => 'سيارتي',
                'brand' => 'Toyota',
                'model' => 'Camry',
                'color' => 'White',
                'plate' => 'RXA 4821',
                'photo_url' => null,
                'is_default' => 0,
                'created_at' => '2026-07-03 06:29:42',
                'updated_at' => '2026-07-03 07:09:28',
            ],
            [
                'id' => 11,
                'customer_id' => 10,
                'name' => 'test',
                'brand' => 'تويوتا',
                'model' => 'Camry',
                'color' => 'أبيض',
                'plate' => '1234',
                'photo_url' => null,
                'is_default' => 1,
                'created_at' => '2026-07-03 07:09:28',
                'updated_at' => '2026-07-03 07:09:28',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('vehicles')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

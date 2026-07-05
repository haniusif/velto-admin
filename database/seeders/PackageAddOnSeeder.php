<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `package_add_ons` (12 rows).
 * Regenerated from the live `velto_admin` database.
 */
class PackageAddOnSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('package_add_ons')->truncate();

        $rows = [
            [
                'id' => 1,
                'wash_package_id' => 1,
                'name' => 'Tire shine',
                'name_ar' => 'تلميع إطارات',
                'extra_price' => '15.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 0,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 2,
                'wash_package_id' => 1,
                'name' => 'Air freshener',
                'name_ar' => 'فواحة',
                'extra_price' => '10.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 3,
                'wash_package_id' => 2,
                'name' => 'Premium scent',
                'name_ar' => 'معطر فاخر',
                'extra_price' => '20.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 0,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 4,
                'wash_package_id' => 2,
                'name' => 'Air freshener',
                'name_ar' => 'فواحة',
                'extra_price' => '10.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 5,
                'wash_package_id' => 2,
                'name' => 'Leather conditioner',
                'name_ar' => 'علاج الجلد',
                'extra_price' => '35.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 2,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 6,
                'wash_package_id' => 3,
                'name' => 'Premium scent',
                'name_ar' => 'معطر فاخر',
                'extra_price' => '20.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 0,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 7,
                'wash_package_id' => 3,
                'name' => 'Air freshener',
                'name_ar' => 'فواحة',
                'extra_price' => '10.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 8,
                'wash_package_id' => 3,
                'name' => 'Leather conditioner',
                'name_ar' => 'علاج الجلد',
                'extra_price' => '35.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 2,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 9,
                'wash_package_id' => 3,
                'name' => 'Engine bay clean',
                'name_ar' => 'تنظيف المحرك',
                'extra_price' => '60.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 3,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 10,
                'wash_package_id' => 3,
                'name' => 'Headlight polish',
                'name_ar' => 'تلميع الإضاءة',
                'extra_price' => '45.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 4,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 11,
                'wash_package_id' => 4,
                'name' => 'Premium scent every visit',
                'name_ar' => 'معطر فاخر بكل زيارة',
                'extra_price' => '60.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 0,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
            [
                'id' => 12,
                'wash_package_id' => 4,
                'name' => 'Air freshener every visit',
                'name_ar' => 'فواحة بكل زيارة',
                'extra_price' => '30.00',
                'icon' => null,
                'is_active' => 1,
                'sort_order' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-05-10 17:30:31',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('package_add_ons')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

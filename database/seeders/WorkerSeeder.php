<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `workers` (6 rows).
 * Regenerated from the live `velto_admin` database.
 */
class WorkerSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('workers')->truncate();

        $rows = [
            [
                'id' => 1,
                'name' => 'Ahmed Mahmoud',
                'phone' => '+966540000001',
                'email' => 'ahmed@velto-sa.com',
                'national_id' => null,
                'city' => 'Riyadh',
                'status' => 'active',
                'preferred_language' => 'ar',
                'last_login_at' => '2026-06-24 06:04:53',
                'hire_date' => '2025-08-16',
                'rating' => '4.80',
                'notes' => null,
                'avatar_url' => null,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-06-24 06:04:53',
            ],
            [
                'id' => 2,
                'name' => 'Mohammed Salem',
                'phone' => '+966540000002',
                'email' => 'mohammed@velto-sa.com',
                'national_id' => null,
                'city' => 'Riyadh',
                'status' => 'active',
                'preferred_language' => 'ar',
                'last_login_at' => null,
                'hire_date' => '2026-02-06',
                'rating' => '4.70',
                'notes' => null,
                'avatar_url' => null,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-06-13 22:54:30',
            ],
            [
                'id' => 3,
                'name' => 'Yousef Al Otaibi',
                'phone' => '+966540000003',
                'email' => 'yousef@velto-sa.com',
                'national_id' => null,
                'city' => 'Riyadh',
                'status' => 'active',
                'preferred_language' => 'ar',
                'last_login_at' => null,
                'hire_date' => '2025-01-14',
                'rating' => '4.90',
                'notes' => null,
                'avatar_url' => null,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-06-13 22:54:30',
            ],
            [
                'id' => 4,
                'name' => 'Rashed Al Dosari',
                'phone' => '+966540000004',
                'email' => 'rashed@velto-sa.com',
                'national_id' => null,
                'city' => 'Riyadh',
                'status' => 'active',
                'preferred_language' => 'ar',
                'last_login_at' => null,
                'hire_date' => '2025-04-21',
                'rating' => '4.60',
                'notes' => null,
                'avatar_url' => null,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-06-13 22:54:30',
            ],
            [
                'id' => 5,
                'name' => 'Tariq Al Mutairi',
                'phone' => '+966540000005',
                'email' => 'tariq@velto-sa.com',
                'national_id' => null,
                'city' => 'Riyadh',
                'status' => 'inactive',
                'preferred_language' => 'ar',
                'last_login_at' => null,
                'hire_date' => '2025-03-19',
                'rating' => '4.50',
                'notes' => null,
                'avatar_url' => null,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-06-13 22:54:30',
            ],
            [
                'id' => 6,
                'name' => 'هاني',
                'phone' => '+966535097129',
                'email' => null,
                'national_id' => null,
                'city' => 'Riyadh',
                'status' => 'active',
                'preferred_language' => 'ar',
                'last_login_at' => '2026-07-03 06:57:39',
                'hire_date' => null,
                'rating' => null,
                'notes' => null,
                'avatar_url' => null,
                'created_at' => '2026-06-24 09:11:59',
                'updated_at' => '2026-07-03 06:57:39',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('workers')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

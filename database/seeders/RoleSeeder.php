<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `roles` (1 rows).
 * Regenerated from the live `velto_admin` database.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('roles')->truncate();

        $rows = [
            [
                'id' => 1,
                'name' => 'super_admin',
                'guard_name' => 'web',
                'created_at' => '2026-05-10 16:52:56',
                'updated_at' => '2026-05-10 16:52:56',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('roles')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

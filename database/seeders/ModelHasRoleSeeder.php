<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `model_has_roles` (2 rows).
 * Regenerated from the live `velto_admin` database.
 */
class ModelHasRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('model_has_roles')->truncate();

        $rows = [
            [
                'role_id' => 1,
                'model_type' => 'App\\Models\\User',
                'model_id' => 1,
            ],
            [
                'role_id' => 1,
                'model_type' => 'App\\Models\\User',
                'model_id' => 2,
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('model_has_roles')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

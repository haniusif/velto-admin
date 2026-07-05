<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `model_has_permissions` (0 rows).
 * Regenerated from the live `velto_admin` database.
 */
class ModelHasPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('model_has_permissions')->truncate();

        // table 'model_has_permissions' had no rows at generation time
        DB::table('model_has_permissions')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

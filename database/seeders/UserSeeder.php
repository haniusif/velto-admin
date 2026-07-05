<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Single super-admin (admin@velto.sa / admin@123).
 * Role assignment lives in ModelHasRoleSeeder (super_admin -> id 1).
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->truncate();

        $rows = [
            [
                'id' => 1,
                'name' => 'Admin',
                'email' => 'admin@velto.sa',
                'email_verified_at' => null,
                'password' => '$2y$12$8WA25E8jNtQgp6qCrtQtjeV7K40Ypw065BzuFHN8kVzNC.XhKqLwS',
                'remember_token' => null,
                'created_at' => '2026-07-05 01:24:26',
                'updated_at' => '2026-07-05 01:24:26',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

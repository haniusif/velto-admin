<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `users` (3 rows).
 * Regenerated from the live `velto_admin` database.
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
                'name' => 'Hani',
                'email' => 'haniusif@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$.jrNHHrOvI8UFV3OwiOXa.n69Bb2kR.LPDYRG0Xydlz93DwfhEqaG',
                'remember_token' => 'TyEJvUrJtINIadc3KFSkwjHZr2THMVG1HITbNJ5H8cGu2JQpluKCTSokuRCa',
                'created_at' => '2026-05-10 16:13:14',
                'updated_at' => '2026-06-09 16:27:39',
            ],
            [
                'id' => 2,
                'name' => 'Admin',
                'email' => 'admin@velto.test',
                'email_verified_at' => null,
                'password' => '$2y$12$sF60rOsxBHyPfyoKd7jIFuy/8zlR7E0OvsyJK6Pw496uYMZ8rb.6m',
                'remember_token' => 'iROSaVR8EfjgXTETzrTA5tCx4hiqfYvLppRaqr9zbZS4Mr1eLRs8PT5BfrTt',
                'created_at' => '2026-07-02 23:20:26',
                'updated_at' => '2026-07-02 23:20:26',
            ],
            [
                'id' => 3,
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

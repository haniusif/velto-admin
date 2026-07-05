<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `sliders` (3 rows).
 * Regenerated from the live `velto_admin` database.
 */
class SliderSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('sliders')->truncate();

        $rows = [
            [
                'id' => 5,
                'image_path' => 'sliders/slide-1.jpg',
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => '2026-05-10 18:14:19',
                'updated_at' => '2026-05-10 18:14:19',
            ],
            [
                'id' => 6,
                'image_path' => 'sliders/slide-2.jpg',
                'sort_order' => 2,
                'is_active' => 1,
                'created_at' => '2026-05-10 18:14:19',
                'updated_at' => '2026-05-10 18:14:19',
            ],
            [
                'id' => 7,
                'image_path' => 'sliders/slide-3.jpg',
                'sort_order' => 3,
                'is_active' => 1,
                'created_at' => '2026-05-10 18:14:19',
                'updated_at' => '2026-05-10 18:14:19',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('sliders')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

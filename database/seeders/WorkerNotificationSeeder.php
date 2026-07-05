<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `worker_notifications` (3 rows).
 * Regenerated from the live `velto_admin` database.
 */
class WorkerNotificationSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('worker_notifications')->truncate();

        $rows = [
            [
                'id' => 1,
                'worker_id' => 1,
                'kind' => 'assigned',
                'title' => 'New job assigned',
                'title_ar' => 'تم إسناد مهمة جديدة',
                'body' => 'Express exterior wash — 2026-06-09 14:00',
                'body_ar' => 'Express exterior wash — 2026-06-09 14:00',
                'data' => '{"appointment_id": 3}',
                'read_at' => '2026-06-13 23:40:15',
                'created_at' => '2026-06-13 23:39:53',
                'updated_at' => '2026-06-13 23:40:15',
            ],
            [
                'id' => 2,
                'worker_id' => 1,
                'kind' => 'assigned',
                'title' => 'New job assigned',
                'title_ar' => 'تم إسناد مهمة جديدة',
                'body' => 'Express exterior wash — 2026-06-09 14:00',
                'body_ar' => 'Express exterior wash — 2026-06-09 14:00',
                'data' => '{"appointment_id": 3}',
                'read_at' => null,
                'created_at' => '2026-06-13 23:44:02',
                'updated_at' => '2026-06-13 23:44:02',
            ],
            [
                'id' => 3,
                'worker_id' => 6,
                'kind' => 'assigned',
                'title' => 'New job assigned',
                'title_ar' => 'تم إسناد مهمة جديدة',
                'body' => 'Express exterior wash — 2026-07-04 09:00',
                'body_ar' => 'غسيل خارجي سريع — 2026-07-04 09:00',
                'data' => '{"appointment_id": 4}',
                'read_at' => null,
                'created_at' => '2026-07-03 06:37:26',
                'updated_at' => '2026-07-03 06:37:26',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('worker_notifications')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `wash_packages` (4 rows).
 * Regenerated from the live `velto_admin` database.
 */
class WashPackageSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('wash_packages')->truncate();

        $rows = [
            [
                'id' => 1,
                'name' => 'Express exterior wash',
                'name_ar' => 'غسيل خارجي سريع',
                'description' => 'Full exterior clean using premium products. Gloss finish, dust and grime removed quickly.',
                'description_ar' => 'تنظيف شامل لجسم السيارة الخارجي باستخدام مواد عالية الجودة تضمن لمعان السيارة وإزالة الأتربة بسرعة.',
                'type' => 'single',
                'price' => '20.00',
                'duration_minutes' => 30,
                'visits_count' => null,
                'validity_days' => null,
                'image_path' => null,
                'is_featured' => 0,
                'is_active' => 1,
                'sort_order' => 1,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-06-09 16:19:32',
            ],
            [
                'id' => 2,
                'name' => 'Interior detail',
                'name_ar' => 'تنظيف داخلي',
                'description' => 'Full vacuum, vents, seats, dashboards, glass and leather. Light scent finish.',
                'description_ar' => 'تنظيف كامل للمقاعد، الأرضيات، الفتحات، الطبلون والزجاج، مع لمسة عطر خفيفة.',
                'type' => 'single',
                'price' => '30.00',
                'duration_minutes' => 90,
                'visits_count' => null,
                'validity_days' => null,
                'image_path' => null,
                'is_featured' => 0,
                'is_active' => 1,
                'sort_order' => 2,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-06-09 16:19:32',
            ],
            [
                'id' => 3,
                'name' => 'Full detail',
                'name_ar' => 'تنظيف متكامل',
                'description' => 'Inside and out — seats, floors, dashboard, tires and plastics. Cabin scent of your choice.',
                'description_ar' => 'يشمل تنظيف السيارة من الخارج والداخل بدقة، بما في ذلك المقاعد والأرضيات والطبلون.',
                'type' => 'single',
                'price' => '40.00',
                'duration_minutes' => 180,
                'visits_count' => null,
                'validity_days' => null,
                'image_path' => null,
                'is_featured' => 1,
                'is_active' => 1,
                'sort_order' => 3,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-06-09 16:19:32',
            ],
            [
                'id' => 4,
                'name' => 'Monthly plan',
                'name_ar' => 'الباقة الشهرية',
                'description' => 'Three visits a month with locked-in slots — mix any service each visit.',
                'description_ar' => 'ثلاث زيارات في الشهر مع إمكانية تغيير الخدمة في كل زيارة، ومواعيد مؤكدة.',
                'type' => 'multi',
                'price' => '150.00',
                'duration_minutes' => 60,
                'visits_count' => 3,
                'validity_days' => 30,
                'image_path' => null,
                'is_featured' => 0,
                'is_active' => 1,
                'sort_order' => 4,
                'created_at' => '2026-05-10 17:30:31',
                'updated_at' => '2026-06-09 16:19:32',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('wash_packages')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

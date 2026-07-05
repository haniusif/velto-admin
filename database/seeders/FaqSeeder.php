<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `faqs` (4 rows).
 * Regenerated from the live `velto_admin` database.
 */
class FaqSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('faqs')->truncate();

        $rows = [
            [
                'id' => 1,
                'question' => 'Do I need a special place to wash the car?',
                'question_ar' => 'هل أحتاج إلى مكان مخصص لغسيل السيارة؟',
                'answer' => 'No. Our team arrives with their own kit and waterless system — a regular parking spot is enough. No water source or drainage required.',
                'answer_ar' => 'لا. فريقنا يصل بأدواته الخاصة ونظام الغسيل اللامائي في الغالب — أي موقف عادي يكفي. لا حاجة لمصدر ماء ولا لتصريف.',
                'sort_order' => 0,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 2,
                'question' => 'Are the products safe for the paint?',
                'question_ar' => 'هل المواد المستخدمة آمنة على طلاء السيارة؟',
                'answer' => 'Yes. We use specialised, tested products that are fully safe for paint and interior surfaces.',
                'answer_ar' => 'نعم. نستخدم مواد متخصصة ومُختبرة، آمنة تمامًا على طلاء السيارة وعلى الأسطح الداخلية.',
                'sort_order' => 1,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 3,
                'question' => 'Do you keep to your schedule?',
                'question_ar' => 'هل تلتزمون بالمواعيد؟',
                'answer' => 'Yes — punctuality is core to the service. You are notified when the team is on its way and when they arrive.',
                'answer_ar' => 'نعم، الالتزام بالموعد جزء أساسي من الخدمة. تصلك إشعارات عند انطلاق الفريق وعند وصوله.',
                'sort_order' => 2,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 4,
                'question' => 'How do I rate the quality of the service?',
                'question_ar' => 'كيف أقيّم جودة خدمتكم؟',
                'answer' => 'After every visit you get a quick in-app rating. Your feedback goes straight to our quality team.',
                'answer_ar' => 'بعد كل زيارة يصلك تقييم سريع داخل التطبيق. ملاحظاتك تذهب مباشرة إلى فريق الجودة.',
                'sort_order' => 3,
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('faqs')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

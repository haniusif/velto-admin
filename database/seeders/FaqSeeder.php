<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'question' => 'Do I need a special place to wash the car?',
                'question_ar' => 'هل أحتاج إلى مكان مخصص لغسيل السيارة؟',
                'answer' => 'No. Our team arrives with their own kit and waterless system — a regular parking spot is enough. No water source or drainage required.',
                'answer_ar' => 'لا. فريقنا يصل بأدواته الخاصة ونظام الغسيل اللامائي في الغالب — أي موقف عادي يكفي. لا حاجة لمصدر ماء ولا لتصريف.',
            ],
            [
                'question' => 'Are the products safe for the paint?',
                'question_ar' => 'هل المواد المستخدمة آمنة على طلاء السيارة؟',
                'answer' => 'Yes. We use specialised, tested products that are fully safe for paint and interior surfaces.',
                'answer_ar' => 'نعم. نستخدم مواد متخصصة ومُختبرة، آمنة تمامًا على طلاء السيارة وعلى الأسطح الداخلية.',
            ],
            [
                'question' => 'Do you keep to your schedule?',
                'question_ar' => 'هل تلتزمون بالمواعيد؟',
                'answer' => 'Yes — punctuality is core to the service. You are notified when the team is on its way and when they arrive.',
                'answer_ar' => 'نعم، الالتزام بالموعد جزء أساسي من الخدمة. تصلك إشعارات عند انطلاق الفريق وعند وصوله.',
            ],
            [
                'question' => 'How do I rate the quality of the service?',
                'question_ar' => 'كيف أقيّم جودة خدمتكم؟',
                'answer' => 'After every visit you get a quick in-app rating. Your feedback goes straight to our quality team.',
                'answer_ar' => 'بعد كل زيارة يصلك تقييم سريع داخل التطبيق. ملاحظاتك تذهب مباشرة إلى فريق الجودة.',
            ],
        ];

        foreach ($rows as $i => $r) {
            Faq::updateOrCreate(
                ['question' => $r['question']],
                $r + ['sort_order' => $i, 'is_active' => true]
            );
        }
    }
}

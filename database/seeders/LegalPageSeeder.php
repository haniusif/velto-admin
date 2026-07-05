<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `legal_pages` (2 rows).
 * Regenerated from the live `velto_admin` database.
 */
class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('legal_pages')->truncate();

        $rows = [
            [
                'id' => 1,
                'slug' => 'terms',
                'title' => 'Terms of Service',
                'title_ar' => 'الشروط والأحكام',
                'body' => 'Welcome to Velto. These terms govern your use of the Velto mobile car-care app and the services delivered by our team.

1. Service area
Velto operates within the service zones currently published in the app. Bookings outside these zones cannot be guaranteed.

2. Bookings & cancellations
You may cancel a booking up to 60 minutes before the scheduled time at no charge. Late cancellations may be charged.

3. Vehicle condition
We take every care with your vehicle, but we are not liable for pre-existing damage. Please flag concerns to the detailer at the start of the visit.

4. Payments
All prices are in SAR. Payments are processed via the providers integrated in the app.

5. Contact
For questions, reach support@velto.sa.',
                'body_ar' => 'مرحباً بك في Velto. تنظم هذه الشروط استخدامك لتطبيق Velto للعناية المتنقلة بالسيارات والخدمات التي يقدمها فريقنا.

١. منطقة الخدمة
يعمل Velto ضمن مناطق الخدمة المعتمدة في التطبيق. لا يمكن ضمان الحجوزات خارج هذه المناطق.

٢. الحجوزات والإلغاء
يمكنك إلغاء الحجز قبل ساعة من موعده دون رسوم. قد تُحتسب رسوم إلغاء متأخر.

٣. حالة السيارة
نعتني بسيارتك بأقصى دقة، لكننا لا نتحمل مسؤولية أي ضرر سابق. يرجى إبلاغ المتخصص بأي ملاحظات قبل بدء الخدمة.

٤. المدفوعات
جميع الأسعار بالريال السعودي وتتم المعالجة عبر مزودات الدفع المدمجة في التطبيق.

٥. التواصل
لأي استفسار، تواصل مع support@velto.sa.',
                'version' => '1.0',
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 2,
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'title_ar' => 'سياسة الخصوصية',
                'body' => 'Velto respects your privacy. We collect only what\'s needed to deliver the service: name, phone, address for the visit, and your vehicle details.

We do not sell or share your data with third parties beyond the operational partners required to deliver the booking. You can request deletion of your account at any time via support@velto.sa.',
                'body_ar' => 'يحترم Velto خصوصيتك. نجمع فقط البيانات اللازمة لتقديم الخدمة: الاسم، الجوال، عنوان الزيارة، وبيانات سيارتك.

لا نبيع أو نشارك بياناتك مع أي طرف ثالث خارج الشركاء التشغيليين اللازمين لإتمام الحجز. يمكنك طلب حذف حسابك في أي وقت عبر support@velto.sa.',
                'version' => '1.0',
                'is_active' => 1,
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('legal_pages')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

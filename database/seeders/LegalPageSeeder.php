<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'terms',
                'title' => 'Terms of Service',
                'title_ar' => 'الشروط والأحكام',
                'body' => "Welcome to Velto. These terms govern your use of the Velto mobile car-care app and the services delivered by our team.\n\n1. Service area\nVelto operates within the service zones currently published in the app. Bookings outside these zones cannot be guaranteed.\n\n2. Bookings & cancellations\nYou may cancel a booking up to 60 minutes before the scheduled time at no charge. Late cancellations may be charged.\n\n3. Vehicle condition\nWe take every care with your vehicle, but we are not liable for pre-existing damage. Please flag concerns to the detailer at the start of the visit.\n\n4. Payments\nAll prices are in SAR. Payments are processed via the providers integrated in the app.\n\n5. Contact\nFor questions, reach support@velto.sa.",
                'body_ar' => "مرحباً بك في Velto. تنظم هذه الشروط استخدامك لتطبيق Velto للعناية المتنقلة بالسيارات والخدمات التي يقدمها فريقنا.\n\n١. منطقة الخدمة\nيعمل Velto ضمن مناطق الخدمة المعتمدة في التطبيق. لا يمكن ضمان الحجوزات خارج هذه المناطق.\n\n٢. الحجوزات والإلغاء\nيمكنك إلغاء الحجز قبل ساعة من موعده دون رسوم. قد تُحتسب رسوم إلغاء متأخر.\n\n٣. حالة السيارة\nنعتني بسيارتك بأقصى دقة، لكننا لا نتحمل مسؤولية أي ضرر سابق. يرجى إبلاغ المتخصص بأي ملاحظات قبل بدء الخدمة.\n\n٤. المدفوعات\nجميع الأسعار بالريال السعودي وتتم المعالجة عبر مزودات الدفع المدمجة في التطبيق.\n\n٥. التواصل\nلأي استفسار، تواصل مع support@velto.sa.",
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'title_ar' => 'سياسة الخصوصية',
                'body' => "Velto respects your privacy. We collect only what's needed to deliver the service: name, phone, address for the visit, and your vehicle details.\n\nWe do not sell or share your data with third parties beyond the operational partners required to deliver the booking. You can request deletion of your account at any time via support@velto.sa.",
                'body_ar' => "يحترم Velto خصوصيتك. نجمع فقط البيانات اللازمة لتقديم الخدمة: الاسم، الجوال، عنوان الزيارة، وبيانات سيارتك.\n\nلا نبيع أو نشارك بياناتك مع أي طرف ثالث خارج الشركاء التشغيليين اللازمين لإتمام الحجز. يمكنك طلب حذف حسابك في أي وقت عبر support@velto.sa.",
            ],
        ];

        foreach ($pages as $p) {
            LegalPage::updateOrCreate(
                ['slug' => $p['slug']],
                $p + ['version' => '1.0', 'is_active' => true]
            );
        }
    }
}

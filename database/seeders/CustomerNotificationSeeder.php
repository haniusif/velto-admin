<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerNotification;
use Illuminate\Database\Seeder;

class CustomerNotificationSeeder extends Seeder
{
    public function run(): void
    {
        Customer::query()->take(5)->get()->each(function (Customer $c) {
            // Refresh demo notifications.
            $c->customerNotifications()->delete();

            $rows = [
                [
                    'kind' => CustomerNotification::KIND_BOOKING,
                    'title' => 'Booking confirmed',
                    'title_ar' => 'تم تأكيد الحجز',
                    'body' => 'Your wash is scheduled for tomorrow at 10:30.',
                    'body_ar' => 'تم جدولة الغسيل غداً الساعة ١٠:٣٠.',
                    'days' => 0,
                    'read' => false,
                ],
                [
                    'kind' => CustomerNotification::KIND_ON_THE_WAY,
                    'title' => 'Detailer is on the way',
                    'title_ar' => 'المتخصص في الطريق',
                    'body' => 'Ahmed will arrive in ~10 minutes.',
                    'body_ar' => 'سيصل أحمد خلال ١٠ دقائق تقريباً.',
                    'days' => 1,
                    'read' => false,
                ],
                [
                    'kind' => CustomerNotification::KIND_COMPLETED,
                    'title' => 'Wash completed',
                    'title_ar' => 'تم إنجاز الغسيل',
                    'body' => 'Tap to rate your experience.',
                    'body_ar' => 'اضغط لتقييم التجربة.',
                    'days' => 3,
                    'read' => true,
                ],
                [
                    'kind' => CustomerNotification::KIND_PROMO,
                    'title' => '20% off your next wash',
                    'title_ar' => 'خصم ٢٠٪ على غسيلك القادم',
                    'body' => 'Use code VELTO20 this week.',
                    'body_ar' => 'استخدم الكود VELTO20 هذا الأسبوع.',
                    'days' => 5,
                    'read' => true,
                ],
            ];

            foreach ($rows as $r) {
                $n = $c->customerNotifications()->create([
                    'kind' => $r['kind'],
                    'title' => $r['title'],
                    'title_ar' => $r['title_ar'],
                    'body' => $r['body'],
                    'body_ar' => $r['body_ar'],
                    'read_at' => $r['read'] ? now()->subDays($r['days'])->addHours(2) : null,
                ]);
                $n->update(['created_at' => now()->subDays($r['days'])]);
            }
        });
    }
}

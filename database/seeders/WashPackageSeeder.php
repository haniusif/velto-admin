<?php

namespace Database\Seeders;

use App\Models\PackageAddOn;
use App\Models\WashPackage;
use Illuminate\Database\Seeder;

class WashPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Express exterior wash',
                'name_ar' => 'غسيل خارجي سريع',
                'description' => 'Full exterior clean using premium products. Gloss finish, dust and grime removed quickly.',
                'description_ar' => 'تنظيف شامل لجسم السيارة الخارجي باستخدام مواد عالية الجودة تضمن لمعان السيارة وإزالة الأتربة بسرعة.',
                'type' => 'single',
                'price' => 99,
                'duration_minutes' => 30,
                'sort_order' => 1,
            ],
            [
                'name' => 'Interior detail',
                'name_ar' => 'تنظيف داخلي',
                'description' => 'Full vacuum, vents, seats, dashboards, glass and leather. Light scent finish.',
                'description_ar' => 'تنظيف كامل للمقاعد، الأرضيات، الفتحات، الطبلون والزجاج، مع لمسة عطر خفيفة.',
                'type' => 'single',
                'price' => 189,
                'duration_minutes' => 90,
                'sort_order' => 2,
            ],
            [
                'name' => 'Full detail',
                'name_ar' => 'تنظيف متكامل',
                'description' => 'Inside and out — seats, floors, dashboard, tires and plastics. Cabin scent of your choice.',
                'description_ar' => 'يشمل تنظيف السيارة من الخارج والداخل بدقة، بما في ذلك المقاعد والأرضيات والطبلون.',
                'type' => 'single',
                'price' => 349,
                'duration_minutes' => 180,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Monthly plan',
                'name_ar' => 'الباقة الشهرية',
                'description' => 'Four visits, locked-in slots, mix any service each visit. Save 20% vs single bookings.',
                'description_ar' => 'أربع زيارات في الشهر مع إمكانية تغيير الخدمة في كل زيارة. وفّر ٢٠٪ مقارنة بالحجوزات الفردية.',
                'type' => 'multi',
                'price' => 499,
                'duration_minutes' => 60,
                'visits_count' => 4,
                'validity_days' => 30,
                'sort_order' => 4,
            ],
        ];

        foreach ($packages as $row) {
            WashPackage::updateOrCreate(
                ['name' => $row['name']],
                $row + ['is_active' => true]
            );
        }

        // Add-ons attached per package
        $addOnsByPackage = [
            'Express exterior wash' => [
                ['name' => 'Tire shine',    'name_ar' => 'تلميع إطارات', 'extra_price' => 15],
                ['name' => 'Air freshener', 'name_ar' => 'فواحة',        'extra_price' => 10],
            ],
            'Interior detail' => [
                ['name' => 'Premium scent',     'name_ar' => 'معطر فاخر',     'extra_price' => 20],
                ['name' => 'Air freshener',     'name_ar' => 'فواحة',         'extra_price' => 10],
                ['name' => 'Leather conditioner','name_ar' => 'علاج الجلد',   'extra_price' => 35],
            ],
            'Full detail' => [
                ['name' => 'Premium scent',     'name_ar' => 'معطر فاخر',     'extra_price' => 20],
                ['name' => 'Air freshener',     'name_ar' => 'فواحة',         'extra_price' => 10],
                ['name' => 'Leather conditioner','name_ar' => 'علاج الجلد',   'extra_price' => 35],
                ['name' => 'Engine bay clean',  'name_ar' => 'تنظيف المحرك',  'extra_price' => 60],
                ['name' => 'Headlight polish',  'name_ar' => 'تلميع الإضاءة', 'extra_price' => 45],
            ],
            'Monthly plan' => [
                ['name' => 'Premium scent every visit', 'name_ar' => 'معطر فاخر بكل زيارة', 'extra_price' => 60],
                ['name' => 'Air freshener every visit', 'name_ar' => 'فواحة بكل زيارة',     'extra_price' => 30],
            ],
        ];

        foreach ($addOnsByPackage as $packageName => $addOns) {
            $package = WashPackage::where('name', $packageName)->first();
            if (! $package) {
                continue;
            }

            foreach ($addOns as $i => $row) {
                PackageAddOn::updateOrCreate(
                    ['wash_package_id' => $package->id, 'name' => $row['name']],
                    $row + [
                        'sort_order' => $i,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}

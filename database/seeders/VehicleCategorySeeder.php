<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'code' => 'A',
                'name' => 'Large',
                'name_ar' => 'كبيرة',
                'description' => 'SUV, pickup, van',
                'description_ar' => 'دفع رباعي، بيك أب، فان',
                'sort_order' => 0,
                'is_active' => 1,
            ],
            [
                'code' => 'B',
                'name' => 'Medium',
                'name_ar' => 'متوسطة',
                'description' => 'Sedan, hatchback',
                'description_ar' => 'سيدان، هاتشباك',
                'sort_order' => 1,
                'is_active' => 1,
            ],
            [
                'code' => 'C',
                'name' => 'Small',
                'name_ar' => 'صغيرة',
                'description' => 'Compact, city car',
                'description_ar' => 'مدمجة، سيارة مدينة',
                'sort_order' => 2,
                'is_active' => 1,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('vehicle_categories')->updateOrInsert(
                ['code' => $row['code']],
                $row + ['created_at' => now(), 'updated_at' => now()],
            );
        }
    }
}

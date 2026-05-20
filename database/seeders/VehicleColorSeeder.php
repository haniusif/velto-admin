<?php

namespace Database\Seeders;

use App\Models\VehicleColor;
use Illuminate\Database\Seeder;

class VehicleColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['slug' => 'white',  'name' => 'White',  'name_ar' => 'أبيض',     'hex' => '#FFFFFF', 'light' => true],
            ['slug' => 'black',  'name' => 'Black',  'name_ar' => 'أسود',     'hex' => '#0A0A0A', 'light' => false],
            ['slug' => 'silver', 'name' => 'Silver', 'name_ar' => 'فضي',      'hex' => '#C0C0C0', 'light' => false],
            ['slug' => 'gray',   'name' => 'Gray',   'name_ar' => 'رمادي',    'hex' => '#6E6E73', 'light' => false],
            ['slug' => 'red',    'name' => 'Red',    'name_ar' => 'أحمر',     'hex' => '#D7484F', 'light' => false],
            ['slug' => 'blue',   'name' => 'Blue',   'name_ar' => 'أزرق',     'hex' => '#1B6EE2', 'light' => false],
            ['slug' => 'navy',   'name' => 'Navy',   'name_ar' => 'كحلي',     'hex' => '#002C5F', 'light' => false],
            ['slug' => 'green',  'name' => 'Green',  'name_ar' => 'أخضر',     'hex' => '#2E7D4F', 'light' => false],
            ['slug' => 'gold',   'name' => 'Gold',   'name_ar' => 'ذهبي',     'hex' => '#C9A961', 'light' => false],
            ['slug' => 'beige',  'name' => 'Beige',  'name_ar' => 'بيج',      'hex' => '#D9C8A4', 'light' => true],
            ['slug' => 'brown',  'name' => 'Brown',  'name_ar' => 'بني',      'hex' => '#6B4E2E', 'light' => false],
            ['slug' => 'orange', 'name' => 'Orange', 'name_ar' => 'برتقالي',  'hex' => '#E6822E', 'light' => false],
            ['slug' => 'yellow', 'name' => 'Yellow', 'name_ar' => 'أصفر',     'hex' => '#E6C42E', 'light' => false],
        ];

        foreach ($colors as $i => $c) {
            VehicleColor::updateOrCreate(
                ['slug' => $c['slug']],
                [
                    'name' => $c['name'],
                    'name_ar' => $c['name_ar'],
                    'hex' => $c['hex'],
                    'is_light_swatch' => $c['light'],
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );
        }
    }
}

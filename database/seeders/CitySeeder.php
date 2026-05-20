<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Riyadh',  'name_ar' => 'الرياض',  'slug' => 'riyadh',  'latitude' => 24.7136, 'longitude' => 46.6753],
            ['name' => 'Jeddah',  'name_ar' => 'جدة',     'slug' => 'jeddah',  'latitude' => 21.4858, 'longitude' => 39.1925],
            ['name' => 'Mecca',   'name_ar' => 'مكة',     'slug' => 'mecca',   'latitude' => 21.3891, 'longitude' => 39.8579],
            ['name' => 'Medina',  'name_ar' => 'المدينة', 'slug' => 'medina',  'latitude' => 24.5247, 'longitude' => 39.5692],
            ['name' => 'Dammam',  'name_ar' => 'الدمام',  'slug' => 'dammam',  'latitude' => 26.4207, 'longitude' => 50.0888],
            ['name' => 'Khobar',  'name_ar' => 'الخبر',   'slug' => 'khobar',  'latitude' => 26.2794, 'longitude' => 50.2083],
            ['name' => 'Dhahran', 'name_ar' => 'الظهران', 'slug' => 'dhahran', 'latitude' => 26.2361, 'longitude' => 50.0393],
            ['name' => 'Tabuk',   'name_ar' => 'تبوك',    'slug' => 'tabuk',   'latitude' => 28.3998, 'longitude' => 36.5700],
            ['name' => 'Abha',    'name_ar' => 'أبها',    'slug' => 'abha',    'latitude' => 18.2164, 'longitude' => 42.5053],
            ['name' => 'Taif',    'name_ar' => 'الطائف',  'slug' => 'taif',    'latitude' => 21.4373, 'longitude' => 40.5127],
        ];

        foreach ($cities as $row) {
            City::updateOrCreate(
                ['slug' => $row['slug']],
                $row + ['country' => 'SA', 'is_active' => true]
            );
        }
    }
}

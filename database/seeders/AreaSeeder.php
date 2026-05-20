<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areasByCity = [
            'riyadh' => [
                ['name' => 'Olaya',              'name_ar' => 'العليا',         'lat' => 24.6904, 'lng' => 46.6850],
                ['name' => 'Al Malqa',           'name_ar' => 'الملقا',         'lat' => 24.8045, 'lng' => 46.6293],
                ['name' => 'Al Yasmin',          'name_ar' => 'الياسمين',       'lat' => 24.8312, 'lng' => 46.6398],
                ['name' => 'Hittin',             'name_ar' => 'حطين',           'lat' => 24.7691, 'lng' => 46.6177],
                ['name' => 'Diplomatic Quarter', 'name_ar' => 'الحي الدبلوماسي', 'lat' => 24.6690, 'lng' => 46.6224],
                ['name' => 'Al Nakheel',         'name_ar' => 'النخيل',         'lat' => 24.7619, 'lng' => 46.6394],
                ['name' => 'Al Sahafa',          'name_ar' => 'الصحافة',        'lat' => 24.8011, 'lng' => 46.6491],
                ['name' => 'Al Murabba',         'name_ar' => 'المربع',         'lat' => 24.6589, 'lng' => 46.7174],
            ],
            'jeddah' => [
                ['name' => 'Al Salamah',         'name_ar' => 'السلامة',        'lat' => 21.5945, 'lng' => 39.1707],
                ['name' => 'Al Hamra',           'name_ar' => 'الحمراء',        'lat' => 21.5128, 'lng' => 39.1764],
                ['name' => 'Ar Rawdah',          'name_ar' => 'الروضة',         'lat' => 21.5666, 'lng' => 39.1701],
            ],
            'dammam' => [
                ['name' => 'Al Faisaliyah',      'name_ar' => 'الفيصلية',       'lat' => 26.4101, 'lng' => 50.0893],
                ['name' => 'Al Shati',           'name_ar' => 'الشاطئ',         'lat' => 26.4499, 'lng' => 50.1163],
            ],
        ];

        foreach ($areasByCity as $citySlug => $areas) {
            $city = City::where('slug', $citySlug)->first();
            if (! $city) {
                continue;
            }

            foreach ($areas as $row) {
                Area::updateOrCreate(
                    ['city_id' => $city->id, 'name' => $row['name']],
                    [
                        'name_ar' => $row['name_ar'],
                        'latitude' => $row['lat'],
                        'longitude' => $row['lng'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}

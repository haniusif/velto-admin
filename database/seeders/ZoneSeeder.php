<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'area' => 'Olaya',
                'name' => 'Olaya Core',
                'name_ar' => 'قلب العليا',
                'color' => '#8863E5',
                'center' => [24.6904, 46.6850],
            ],
            [
                'area' => 'Al Malqa',
                'name' => 'Al Malqa North',
                'name_ar' => 'الملقا الشمالية',
                'color' => '#B38BEE',
                'center' => [24.8120, 46.6300],
            ],
        ];

        foreach ($zones as $row) {
            $area = Area::where('name', $row['area'])->first();
            if (! $area) {
                continue;
            }

            Zone::updateOrCreate(
                ['area_id' => $area->id, 'name' => $row['name']],
                [
                    'name_ar' => $row['name_ar'],
                    'color' => $row['color'],
                    'is_active' => true,
                    'geometry' => self::squareAround($row['center'][0], $row['center'][1], 0.012),
                ]
            );
        }
    }

    private static function squareAround(float $lat, float $lng, float $r): array
    {
        return [
            'type' => 'Feature',
            'properties' => new \stdClass(),
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[
                    [$lng - $r, $lat - $r],
                    [$lng + $r, $lat - $r],
                    [$lng + $r, $lat + $r],
                    [$lng - $r, $lat + $r],
                    [$lng - $r, $lat - $r],
                ]],
            ],
        ];
    }
}

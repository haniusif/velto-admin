<?php

namespace Database\Seeders;

use App\Models\VehicleBrand;
use App\Models\VehicleModelEntry;
use Illuminate\Database\Seeder;

class VehicleBrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'slug' => 'toyota', 'name' => 'Toyota', 'name_ar' => 'تويوتا',
                'icon_path' => 'brands/toyota.svg',
                'models' => ['Camry', 'Corolla', 'Land Cruiser', 'Hilux', 'RAV4', 'Yaris', 'Prado', 'Avalon'],
            ],
            [
                'slug' => 'lexus', 'name' => 'Lexus', 'name_ar' => 'لكزس',
                'icon_path' => 'brands/lexus.svg',
                'models' => ['ES', 'IS', 'LS', 'LX', 'NX', 'RX', 'UX', 'GX'],
            ],
            [
                'slug' => 'hyundai', 'name' => 'Hyundai', 'name_ar' => 'هيونداي',
                'icon_path' => 'brands/hyundai.svg',
                'models' => ['Sonata', 'Elantra', 'Accent', 'Tucson', 'Santa Fe', 'Kona', 'Palisade', 'Azera'],
            ],
            [
                'slug' => 'nissan', 'name' => 'Nissan', 'name_ar' => 'نيسان',
                'icon_path' => 'brands/nissan.svg',
                'models' => ['Altima', 'Sunny', 'Maxima', 'Patrol', 'X-Trail', 'Sentra', 'Pathfinder', 'Kicks'],
            ],
            [
                'slug' => 'mercedes_benz', 'name' => 'Mercedes-Benz', 'name_ar' => 'مرسيدس-بنز',
                'icon_path' => 'brands/mercedes_benz.svg',
                'models' => ['C-Class', 'E-Class', 'S-Class', 'GLA', 'GLC', 'GLE', 'GLS', 'G-Class'],
            ],
            [
                'slug' => 'bmw', 'name' => 'BMW', 'name_ar' => 'بي إم دبليو',
                'icon_path' => null,
                'models' => ['3 Series', '5 Series', '7 Series', 'X1', 'X3', 'X5', 'X6', 'X7'],
            ],
        ];

        foreach ($brands as $i => $row) {
            $brand = VehicleBrand::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'name_ar' => $row['name_ar'],
                    'icon_path' => $row['icon_path'],
                    'sort_order' => $i,
                    'is_active' => true,
                ]
            );

            foreach ($row['models'] as $j => $modelName) {
                VehicleModelEntry::updateOrCreate(
                    ['vehicle_brand_id' => $brand->id, 'name' => $modelName],
                    ['sort_order' => $j, 'is_active' => true]
                );
            }
        }
    }
}

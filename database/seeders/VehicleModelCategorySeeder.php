<?php

namespace Database\Seeders;

use App\Models\VehicleCategory;
use App\Models\VehicleModelEntry;
use Illuminate\Database\Seeder;

/**
 * Puts every vehicle model into a size band.
 *
 * Size is what actually varies the work in a wash — a Yaris and a Land Cruiser
 * are not the same job — so the band is attached to the model and is known the
 * moment a customer picks their car.
 *
 * Only the extremes are listed. Large is full-size SUVs, pickups, vans and
 * long-wheelbase luxury; Small is hatchbacks and subcompacts. Everything not
 * named falls to Medium, which is where most sedans and compact SUVs sit — the
 * common case is the default rather than something to enumerate and get wrong.
 *
 * Matched on the model's own name, so a model shared across brands (several
 * makes sell a "Rio"-sized car) is classified consistently.
 */
class VehicleModelCategorySeeder extends Seeder
{
    public function run(): void
    {
        $large = VehicleCategory::firstWhere('name', 'Large');
        $medium = VehicleCategory::firstWhere('name', 'Medium');
        $small = VehicleCategory::firstWhere('name', 'Small');

        if (! $large || ! $medium || ! $small) {
            $this->command?->error('vehicle_categories is missing Small/Medium/Large — nothing classified.');

            return;
        }

        $counts = ['large' => 0, 'small' => 0, 'medium' => 0];

        foreach (VehicleModelEntry::all() as $model) {
            $band = match (true) {
                in_array($model->name, self::LARGE, true) => $large,
                in_array($model->name, self::SMALL, true) => $small,
                default => $medium,
            };

            $model->update(['vehicle_category_id' => $band->id]);
            $counts[strtolower($band->name)]++;
        }

        $this->command?->info(
            "Classified {$counts['small']} small, {$counts['medium']} medium, {$counts['large']} large."
        );
    }

    /** Full-size SUVs, pickups, vans and long-wheelbase luxury. */
    private const LARGE = [
        // Toyota
        'Land Cruiser', 'Land Cruiser Pickup', 'Prado', 'Hilux', 'Fortuner',
        'Highlander', 'Hiace', 'Coaster', 'Previa', 'Innova',
        // Hyundai / Kia
        'Palisade', 'Staria', 'H-1', 'Santa Fe', 'Telluride', 'Carnival', 'Sorento',
        // Nissan
        'Patrol', 'Patrol Safari', 'Armada', 'Pathfinder', 'Navara', 'Urvan', 'Xterra',
        // American
        'Tahoe', 'Suburban', 'Silverado', 'Traverse', 'Blazer',
        'Expedition', 'Explorer', 'F-150', 'Ranger', 'Bronco', 'Everest', 'Transit',
        'Yukon', 'Yukon XL', 'Sierra', 'Acadia', 'Canyon', 'Hummer EV',
        'Escalade', 'Navigator', 'Aviator', 'Durango', 'Ram 1500',
        'Grand Wagoneer', 'Gladiator', 'Grand Cherokee', 'Wrangler', 'Pacifica',
        // European
        'G-Class', 'GLS', 'V-Class', 'Sprinter', 'Maybach',
        'X7', 'Q7', 'Q8', 'Touareg', 'Teramont', 'XC90', 'Cayenne', 'Bentayga',
        'Range Rover', 'Range Rover Sport', 'Range Rover Vogue', 'Defender', 'Discovery',
        // Japanese / other
        'Pajero', 'Montero Sport', 'L200', 'D-Max', 'MU-X', 'NPR', 'Gran Max',
        'QX80', 'QX60', 'LX', 'GX', 'LM', 'Poer', 'Tank 300', 'Tank 500',
        'X90', 'CS95', 'CS85', 'Outlander', 'Levante', 'Dokker',
    ];

    /** Hatchbacks and subcompacts. */
    private const SMALL = [
        'Yaris', 'Raize', 'Urban Cruiser', 'Rush',
        'Accent', 'Grand i10', 'Venue', 'Bayon', 'Kona',
        'Picanto', 'Rio', 'Pegas', 'Sonet',
        'Sunny', 'Micra', 'Juke', 'Kicks',
        'Groove', 'Optra', 'EcoSport',
        'City', 'Polo', 'Golf', 'A3', 'Q3', '1 Series', 'X1', 'A-Class', 'GLA', 'CLA',
        'Mazda 2', 'CX-3', 'MX-5',
        'Attrage', 'Lancer', 'ASX',
        'Baleno', 'Dzire', 'Swift', 'Ciaz', 'Fronx', 'Jimny', 'Ertiga',
        'Symbol', 'Captur', '208', '2008', '301',
        'Tiggo 2', 'Arrizo 5', 'Alsvin', 'Eado', 'CS35',
        'MG5', 'ZS', 'Emgrand', 'Coolray',
        'Sirion', 'Terios', 'XV', 'Impreza', 'XC40',
        '718',
    ];
}

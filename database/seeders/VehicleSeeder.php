<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            ['name' => 'Daily', 'brand' => 'Toyota', 'model' => 'Camry',  'color' => 'White',  'plate' => 'ABC 1234'],
            ['name' => null,    'brand' => 'Lexus',  'model' => 'ES350',  'color' => 'Silver', 'plate' => 'XYZ 9876'],
            ['name' => 'Family','brand' => 'Hyundai','model' => 'Sonata', 'color' => 'Black',  'plate' => 'KSA 4567'],
            ['name' => null,    'brand' => 'Nissan', 'model' => 'Patrol', 'color' => 'Beige',  'plate' => 'NSR 8521'],
            ['name' => null,    'brand' => 'BMW',    'model' => 'X5',     'color' => 'Black',  'plate' => 'BMW 7799'],
        ];

        Customer::query()->take(5)->get()->each(function (Customer $c, int $i) use ($samples) {
            $row = $samples[$i % count($samples)];
            Vehicle::updateOrCreate(
                ['customer_id' => $c->id, 'plate' => $row['plate']],
                $row + ['is_default' => true],
            );
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $slots = [
            ['start' => '09:00', 'end' => '10:30', 'capacity' => 3],
            ['start' => '10:30', 'end' => '12:00', 'capacity' => 3],
            ['start' => '14:00', 'end' => '15:30', 'capacity' => 3],
            ['start' => '15:30', 'end' => '17:00', 'capacity' => 3],
            ['start' => '17:00', 'end' => '18:30', 'capacity' => 2],
            ['start' => '18:30', 'end' => '20:00', 'capacity' => 2],
        ];

        $today = CarbonImmutable::today();

        for ($i = 0; $i < 14; $i++) {
            $date = $today->addDays($i);

            foreach ($slots as $slot) {
                TimeSlot::updateOrCreate(
                    ['date' => $date->toDateString(), 'start_time' => $slot['start']],
                    [
                        'end_time' => $slot['end'],
                        'capacity' => $slot['capacity'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Worker;
use Illuminate\Database\Seeder;

class WorkerSeeder extends Seeder
{
    public function run(): void
    {
        $workers = [
            ['name' => 'Ahmed Mahmoud',   'phone' => '+966540000001', 'email' => 'ahmed@velto-sa.com',   'rating' => 4.8],
            ['name' => 'Mohammed Salem',  'phone' => '+966540000002', 'email' => 'mohammed@velto-sa.com','rating' => 4.7],
            ['name' => 'Yousef Al Otaibi','phone' => '+966540000003', 'email' => 'yousef@velto-sa.com',  'rating' => 4.9],
            ['name' => 'Rashed Al Dosari','phone' => '+966540000004', 'email' => 'rashed@velto-sa.com',  'rating' => 4.6],
            ['name' => 'Tariq Al Mutairi','phone' => '+966540000005', 'email' => 'tariq@velto-sa.com',   'rating' => 4.5, 'status' => 'inactive'],
        ];

        foreach ($workers as $row) {
            Worker::updateOrCreate(
                ['phone' => $row['phone']],
                $row + [
                    'city' => 'Riyadh',
                    'status' => $row['status'] ?? 'active',
                    'hire_date' => now()->subDays(rand(60, 540))->toDateString(),
                ]
            );
        }
    }
}

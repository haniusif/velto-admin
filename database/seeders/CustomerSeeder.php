<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Hani Yousif',    'phone' => '+966500000001', 'email' => 'hani@example.sa',   'preferred_language' => 'ar'],
            ['name' => 'Sara Al Otaibi', 'phone' => '+966500000002', 'email' => 'sara@example.sa',   'preferred_language' => 'ar'],
            ['name' => 'Faisal Al Saud', 'phone' => '+966500000003', 'email' => 'faisal@example.sa', 'preferred_language' => 'ar'],
            ['name' => 'Layla Al Harbi', 'phone' => '+966500000004', 'email' => 'layla@example.sa',  'preferred_language' => 'ar'],
            ['name' => 'Omar Al Ghamdi', 'phone' => '+966500000005', 'email' => 'omar@example.sa',   'preferred_language' => 'en'],
            ['name' => 'Rana Al Qahtani','phone' => '+966500000006', 'email' => 'rana@example.sa',   'preferred_language' => 'ar'],
            ['name' => 'Khalid Al Anazi','phone' => '+966500000007', 'email' => 'khalid@example.sa', 'preferred_language' => 'en'],
            ['name' => 'Nora Al Subaie', 'phone' => '+966500000008', 'email' => 'nora@example.sa',   'preferred_language' => 'ar'],
        ];

        foreach ($customers as $row) {
            Customer::updateOrCreate(
                ['phone' => $row['phone']],
                $row + [
                    'city' => 'Riyadh',
                    'status' => 'active',
                    'joined_at' => now()->subDays(rand(1, 90)),
                ]
            );
        }
    }
}

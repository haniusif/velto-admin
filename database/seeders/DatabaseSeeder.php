<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CitySeeder::class,
            AreaSeeder::class,
            ZoneSeeder::class,
            WashPackageSeeder::class,
            TimeSlotSeeder::class,
            CustomerSeeder::class,
            WorkerSeeder::class,
            VehicleBrandSeeder::class,
            VehicleColorSeeder::class,
            CountrySeeder::class,
            FaqSeeder::class,
            LegalPageSeeder::class,
            AppSettingSeeder::class,
            VehicleSeeder::class,
            WalletTransactionSeeder::class,
            CustomerNotificationSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Full data snapshot of the `velto_admin` database.
     * Each child seeder truncates + reinserts its table.
     *
     * Framework/transient tables (migrations, sessions, cache, jobs, ...) and
     * auth artifacts (personal_access_tokens, phone_otps, password_reset_tokens)
     * are intentionally excluded.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->call([
            CountrySeeder::class,
            CitySeeder::class,
            AreaSeeder::class,
            ZoneSeeder::class,
            VehicleBrandSeeder::class,
            VehicleColorSeeder::class,
            VehicleModelEntrySeeder::class,
            WashPackageSeeder::class,
            PackageAddOnSeeder::class,
            TimeSlotSeeder::class,
            UserSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            ModelHasRoleSeeder::class,
            ModelHasPermissionSeeder::class,
            RoleHasPermissionSeeder::class,
            CustomerSeeder::class,
            VehicleSeeder::class,
            WorkerSeeder::class,
            AppointmentSeeder::class,
            PaymentTransactionSeeder::class,
            WalletTransactionSeeder::class,
            CustomerNotificationSeeder::class,
            WorkerNotificationSeeder::class,
            FaqSeeder::class,
            LegalPageSeeder::class,
            SliderSeeder::class,
            AppSettingSeeder::class,
        ]);

        Schema::enableForeignKeyConstraints();
    }
}

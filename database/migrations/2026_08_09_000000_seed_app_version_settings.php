<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Version floors and ceilings for the mobile apps, so a release can be made
 * mandatory without shipping another build.
 *
 * Seeded permissively on purpose: minimum_version 1.0.0 / minimum_build 1
 * forces nobody. Raising the minimum is a deliberate act, done from the admin
 * settings screen — the wrong value here locks every customer out of the app,
 * so the default must be the harmless one.
 *
 * updateOrInsert keyed on `key`: re-running must never clobber a floor the
 * business has since raised.
 */
return new class extends Migration
{
    private const IOS_STORE = 'https://apps.apple.com/app/id6762532453';

    private const ANDROID_STORE = 'https://play.google.com/store/apps/details?id=sa.velto.velto_customer';

    public function up(): void
    {
        $now = now();

        foreach ($this->rows() as $row) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $row['key']],
                $row + ['group' => 'app_version', 'created_at' => $now, 'updated_at' => $now],
            );
        }
    }

    public function down(): void
    {
        DB::table('app_settings')->where('group', 'app_version')->delete();
    }

    /** @return array<int,array<string,string>> */
    private function rows(): array
    {
        $rows = [];

        foreach (['ios' => self::IOS_STORE, 'android' => self::ANDROID_STORE] as $platform => $store) {
            $label = strtoupper($platform);

            $rows[] = [
                'key' => "app_version.{$platform}.latest_version",
                'label' => "{$label} — latest version",
                'value' => '1.0.4',
                'type' => 'string',
            ];
            $rows[] = [
                'key' => "app_version.{$platform}.latest_build",
                'label' => "{$label} — latest build",
                'value' => '23',
                'type' => 'string',
            ];
            $rows[] = [
                'key' => "app_version.{$platform}.minimum_version",
                'label' => "{$label} — minimum version (below this, update is forced)",
                'value' => '1.0.0',
                'type' => 'string',
            ];
            $rows[] = [
                'key' => "app_version.{$platform}.minimum_build",
                'label' => "{$label} — minimum build (below this, update is forced)",
                'value' => '1',
                'type' => 'string',
            ];
            $rows[] = [
                'key' => "app_version.{$platform}.store_url",
                'label' => "{$label} — store URL",
                'value' => $store,
                'type' => 'string',
            ];
        }

        return $rows;
    }
};

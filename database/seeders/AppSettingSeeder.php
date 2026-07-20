<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `app_settings` (8 rows).
 * Regenerated from the live `velto_admin` database.
 */
class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('app_settings')->truncate();

        $rows = [
            [
                'id' => 1,
                'group' => 'support',
                'key' => 'support.phone',
                'label' => 'Support phone',
                'value' => '+966559809687',
                'type' => 'tel',
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 2,
                'group' => 'support',
                'key' => 'support.whatsapp',
                'label' => 'WhatsApp number',
                'value' => '966559809687',
                'type' => 'tel',
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 3,
                'group' => 'support',
                'key' => 'support.email_general',
                'label' => 'General email',
                'value' => 'info@velto.sa',
                'type' => 'email',
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 4,
                'group' => 'support',
                'key' => 'support.email_support',
                'label' => 'Support email',
                'value' => 'support@velto.sa',
                'type' => 'email',
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 5,
                'group' => 'support',
                'key' => 'support.website_display',
                'label' => 'Website (label)',
                'value' => 'www.velto.sa',
                'type' => 'string',
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 6,
                'group' => 'support',
                'key' => 'support.website_url',
                'label' => 'Website URL',
                'value' => 'https://velto.sa/',
                'type' => 'url',
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 7,
                'group' => 'brand',
                'key' => 'brand.name',
                'label' => 'Brand name',
                'value' => 'Velto',
                'type' => 'string',
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 8,
                'group' => 'brand',
                'key' => 'brand.tagline',
                'label' => 'Tagline',
                'value' => 'Mobile car care that comes to you',
                'type' => 'string',
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
            [
                'id' => 9,
                'group' => 'booking',
                'key' => 'booking.pending_grace_minutes',
                'label' => 'مهلة إلغاء الحجز غير المدفوع (دقائق)',
                'value' => '30',
                'type' => 'number',
                'created_at' => '2026-05-10 19:19:57',
                'updated_at' => '2026-05-10 19:19:57',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('app_settings')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}

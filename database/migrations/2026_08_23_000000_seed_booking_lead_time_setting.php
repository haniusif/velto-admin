<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * How long before a wash a customer may still book it.
 *
 * The code defaults to 30 minutes without this row, so the rule works whether
 * or not the setting exists. Seeding it puts the number in the admin, where it
 * can be widened on a busy day without a deploy — which is the point of it
 * being a setting rather than a constant.
 */
return new class extends Migration
{
    private const KEY = 'booking.min_lead_minutes';

    public function up(): void
    {
        if (DB::table('app_settings')->where('key', self::KEY)->exists()) {
            return;
        }

        DB::table('app_settings')->insert([
            'group' => 'booking',
            'key' => self::KEY,
            'label' => 'أقل مهلة للحجز قبل الموعد (دقائق)',
            'value' => '30',
            'type' => 'number',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('app_settings')->where('key', self::KEY)->delete();
    }
};

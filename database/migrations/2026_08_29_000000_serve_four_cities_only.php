<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Velto serves Riyadh, Jeddah, Taif and Yanbu.
 *
 * The cities table was seeded with ten, all switched on, so the app offered
 * Mecca, Medina, Dammam, Khobar, Dhahran, Tabuk and Abha — places nobody can
 * be sent to. A customer picking one would get as far as choosing a time
 * before anything went wrong.
 *
 * The unserved cities are switched off rather than deleted: Dammam carries two
 * areas, and switching a row back on is how this gets undone the day coverage
 * expands. Nothing is attached to any of the seven — no bookings, no
 * customers — so nothing is orphaned either way.
 */
return new class extends Migration
{
    /** Mecca, Medina, Dammam, Khobar, Dhahran, Tabuk, Abha. */
    private const NOT_SERVED = ['Mecca', 'Medina', 'Dammam', 'Khobar', 'Dhahran', 'Tabuk', 'Abha'];

    public function up(): void
    {
        DB::table('cities')->whereIn('name', self::NOT_SERVED)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        // Yanbu was never seeded, so the fourth city could not be chosen at all.
        if (! DB::table('cities')->where('name', 'Yanbu')->exists()) {
            DB::table('cities')->insert([
                'name' => 'Yanbu',
                'name_ar' => 'ينبع',
                'slug' => 'yanbu',
                // Not read from a sibling row: migrations run before the
                // seeders, so on a fresh database there is none to read.
                'country' => 'SA',
                // Yanbu al Bahr.
                'latitude' => 24.0895000,
                'longitude' => 38.0618000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // The three served cities that already existed stay on, explicitly, so
        // this migration states the whole coverage rather than half of it.
        DB::table('cities')->whereIn('name', ['Riyadh', 'Jeddah', 'Taif'])->update([
            'is_active' => true,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('cities')->whereIn('name', self::NOT_SERVED)->update([
            'is_active' => true,
            'updated_at' => now(),
        ]);

        // Yanbu is left in place: by the time this is rolled back it may carry
        // areas or bookings, and dropping the row would take them with it.
        DB::table('cities')->where('name', 'Yanbu')->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);
    }
};

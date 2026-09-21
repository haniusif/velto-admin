<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Codes are now stored as an HMAC-SHA256 (64 hex chars) instead of the
 * four digits themselves. Codes issued before this deploy can no longer be
 * verified; they expire within ten minutes and the app simply asks again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_otps', function (Blueprint $table) {
            $table->string('code', 64)->change();
        });
        // Nothing in flight should be redeemable as plain text.
        \Illuminate\Support\Facades\DB::table('phone_otps')->whereNull('used_at')->update(['used_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('phone_otps', function (Blueprint $table) {
            $table->string('code', 8)->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Addresses a customer has saved from the map pin.
 *
 * Previously these lived in SharedPreferences on the device: lost on
 * reinstall, absent on a second phone, and invisible to support when someone
 * rings up about "my home address". Everything else the customer owns is
 * server-side; this was the exception.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->string('label');
            $table->string('subtitle')->nullable();

            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            // Coverage as checked when the pin was saved. Re-verified at
            // booking time, so this is a hint for the list, not a promise.
            $table->boolean('is_covered')->default(false);

            $table->string('icon_key', 20)->default('place');

            $table->timestamps();

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_addresses');
    }
};

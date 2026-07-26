<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Brands already carry an Arabic name; models did not, so the add-a-car picker
 * showed "Camry" to an Arabic user reading "تويوتا" right above it.
 *
 * Nullable on purpose: a model without an Arabic name falls back to the Latin
 * one, which is what most people write anyway for newer models.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_model_entries', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_model_entries', function (Blueprint $table) {
            $table->dropColumn('name_ar');
        });
    }
};

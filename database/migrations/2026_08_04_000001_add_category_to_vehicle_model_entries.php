<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links a vehicle model to its size category.
 *
 * vehicle_categories (Small / Medium / Large) already existed but nothing
 * referenced it — no model, no vehicle, no price. It was a table with a
 * Filament screen and no meaning.
 *
 * Size is what actually differs between a Yaris and a Land Cruiser for a wash:
 * time on site, water and product used. Attaching it to the model rather than
 * the customer's vehicle means it is known as soon as they pick a car, without
 * asking them to classify it themselves.
 *
 * Nullable: a model added later is unclassified rather than silently "Small",
 * and nullOnDelete so removing a category never deletes models.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_model_entries', function (Blueprint $table) {
            $table->foreignId('vehicle_category_id')
                ->nullable()
                ->after('vehicle_brand_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_model_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_category_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What a wash costs for this size of car.
     *
     * Nullable on purpose: a category with no price falls back to the wash
     * package's own price rather than pricing the job at zero, so adding a
     * fourth size band later cannot accidentally give work away.
     *
     * Deliberately does NOT backfill prices. Landing this column empty means
     * charging is unchanged the moment it deploys — size pricing switches on
     * only when someone types a price into the panel, which is also how it
     * switches back off. The alternative would start charging by size while
     * the shipped app still quotes the service price, and the two would
     * disagree until a new app build reached the stores.
     */
    public function up(): void
    {
        Schema::table('vehicle_categories', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('description_ar');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_categories', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};

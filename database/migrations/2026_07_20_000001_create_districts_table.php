<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('city', 64)->default('Riyadh');
            $table->string('name');       // Arabic neighbourhood name (from KML)
            $table->string('name_ar')->nullable();
            $table->string('slug')->nullable();
            $table->json('geometry');     // GeoJSON Polygon / MultiPolygon
            $table->boolean('is_covered')->default(false);
            $table->timestamps();

            $table->index(['city', 'is_covered']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};

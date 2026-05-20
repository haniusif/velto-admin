<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_model_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_brand_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['vehicle_brand_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_model_entries');
    }
};

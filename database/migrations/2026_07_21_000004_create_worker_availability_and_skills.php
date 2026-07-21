<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('worker_skills', function (Blueprint $table) {
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->primary(['worker_id', 'skill_id']);
        });

        Schema::create('worker_zones', function (Blueprint $table) {
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->primary(['worker_id', 'zone_id']);
        });

        Schema::create('worker_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sunday … 6=Saturday (Carbon dayOfWeek)
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->index(['worker_id', 'day_of_week']);
        });

        Schema::create('worker_time_off', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('type', 16)->default('day_off'); // vacation | day_off | sick
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['worker_id', 'starts_on', 'ends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_time_off');
        Schema::dropIfExists('worker_shifts');
        Schema::dropIfExists('worker_zones');
        Schema::dropIfExists('worker_skills');
        Schema::dropIfExists('skills');
    }
};

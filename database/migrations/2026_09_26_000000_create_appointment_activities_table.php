<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who did what to a booking. The order row only ever held the latest state
 * and a handful of *_at stamps, so "who cancelled this?" or "who moved it to
 * Thursday?" had no answer anywhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();

            $table->string('event', 32); // created | status_changed | updated

            // Snapshotted, not just referenced: a deleted admin or a renamed
            // customer must not rewrite who did something last month.
            $table->string('actor_type', 16); // admin | customer | worker | system
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();

            // {field: [old, new]} for the fields that changed.
            $table->json('changes')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['appointment_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_activities');
    }
};

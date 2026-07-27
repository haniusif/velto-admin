<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where a specialist is right now, while they are on a job.
 *
 * One row per worker, overwritten on each update: this is a live position, not
 * a movement history. Keeping a trail would mean holding a log of where staff
 * have been, which is a liability to store and to justify — and the customer
 * only ever needs "where are they now".
 *
 * The row is deleted when the job ends.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->unique()->constrained()->cascadeOnDelete();

            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('accuracy', 8, 2)->nullable();  // metres
            $table->decimal('heading', 6, 2)->nullable();   // degrees

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_locations');
    }
};

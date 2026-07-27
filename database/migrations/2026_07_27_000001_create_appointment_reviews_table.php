<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer feedback on a completed job.
 *
 * `workers.rating` has existed since the first migration with nothing ever
 * writing to it; this is the source it was waiting for. One review per
 * appointment, enforced by a unique index rather than by hoping the app
 * behaves.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appointment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            // Kept even if the worker record is later removed, so the
            // customer's feedback and the job history stay intact.
            $table->foreignId('worker_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedTinyInteger('rating');   // 1..5
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->index(['worker_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_reviews');
    }
};

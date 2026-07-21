<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            // offered | accepted | rejected | expired | cancelled | assigned(direct)
            $table->string('status', 16)->default('offered');
            $table->decimal('score', 5, 4)->nullable();      // winning worker score [0,1]
            $table->json('factors')->nullable();             // score breakdown for explainability
            $table->string('reason', 64)->nullable();        // why offered / why re-dispatched
            $table->unsignedSmallInteger('attempt')->default(1);
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['appointment_id', 'status']);
            $table->index(['worker_id', 'status', 'offered_at']);
            $table->index(['status', 'expires_at']); // timeout sweep
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_offers');
    }
};

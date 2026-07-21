<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Audit + analytics + ML-training spine: one row per dispatch decision.
        Schema::create('dispatch_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('worker_id')->nullable();
            $table->string('type', 32);        // assigned | offered | accepted | rejected | expired | reassigned | waiting | scheduled
            $table->string('reason', 64)->nullable();
            $table->string('actor', 32)->nullable(); // engine | admin | worker | customer
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['appointment_id']);
            $table->index(['type', 'created_at']);
            $table->index(['worker_id', 'created_at']);
        });

        Schema::table('wash_packages', function (Blueprint $table) {
            $table->foreignId('required_skill_id')->nullable()->after('id')->constrained('skills')->nullOnDelete();
            $table->string('dispatch_strategy', 24)->nullable()->after('required_skill_id');
        });
    }

    public function down(): void
    {
        Schema::table('wash_packages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('required_skill_id');
            $table->dropColumn('dispatch_strategy');
        });
        Schema::dropIfExists('dispatch_events');
    }
};

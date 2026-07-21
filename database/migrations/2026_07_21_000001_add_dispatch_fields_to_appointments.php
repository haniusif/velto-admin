<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Dispatch lifecycle layer sitting above the job `status`.
            $table->string('dispatch_state', 24)->nullable()->after('worker_id');
            // Strategy override for this specific job (else service/global default).
            $table->string('dispatch_strategy', 24)->nullable()->after('dispatch_state');
            // Operator escape hatches.
            $table->boolean('auto_dispatch')->default(true)->after('dispatch_strategy');
            $table->boolean('assignment_locked')->default(false)->after('auto_dispatch');
            // How many times the engine has (re)dispatched this job.
            $table->unsignedSmallInteger('dispatch_attempts')->default(0)->after('assignment_locked');
            $table->unsignedBigInteger('last_offer_id')->nullable()->after('dispatch_attempts');

            $table->index(['dispatch_state', 'scheduled_at']);
            $table->index(['worker_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['dispatch_state', 'scheduled_at']);
            $table->dropIndex(['worker_id', 'scheduled_at']);
            $table->dropColumn([
                'dispatch_state', 'dispatch_strategy', 'auto_dispatch',
                'assignment_locked', 'dispatch_attempts', 'last_offer_id',
            ]);
        });
    }
};

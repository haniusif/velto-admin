<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // The worker assigned to perform this job (set by the admin / dispatcher).
            $table->foreignId('worker_id')->nullable()->after('customer_id')
                ->constrained()->nullOnDelete();

            // Worker job lifecycle timestamps (status stays in the shared enum;
            // these record when the assigned worker acted).
            $table->dateTime('accepted_at')->nullable()->after('completed_at');
            $table->dateTime('started_at')->nullable()->after('accepted_at');

            $table->index(['worker_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['worker_id', 'status']);
            $table->dropConstrainedForeignId('worker_id');
            $table->dropColumn(['accepted_at', 'started_at']);
        });
    }
};

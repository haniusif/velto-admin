<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            // Capacity caps used by the eligibility gates.
            $table->unsignedSmallInteger('max_jobs_per_day')->default(8)->after('rating');
            $table->unsignedSmallInteger('max_concurrent_jobs')->default(1)->after('max_jobs_per_day');
            // Cached acceptance rate (accepted / offered) — recomputed periodically.
            $table->decimal('acceptance_rate', 4, 3)->nullable()->after('max_concurrent_jobs');
            // Duty state (Phase 1 toggle; live location lands in Phase 2).
            $table->boolean('is_online')->default(false)->after('acceptance_rate');
            $table->timestamp('last_seen_at')->nullable()->after('is_online');
            $table->decimal('last_lat', 10, 7)->nullable()->after('last_seen_at');
            $table->decimal('last_lng', 10, 7)->nullable()->after('last_lat');

            $table->index(['status', 'city']);
            $table->index(['is_online', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropIndex(['status', 'city']);
            $table->dropIndex(['is_online', 'last_seen_at']);
            $table->dropColumn([
                'max_jobs_per_day', 'max_concurrent_jobs', 'acceptance_rate',
                'is_online', 'last_seen_at', 'last_lat', 'last_lng',
            ]);
        });
    }
};

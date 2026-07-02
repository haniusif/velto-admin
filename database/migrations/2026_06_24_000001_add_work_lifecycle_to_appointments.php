<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Finer-grained worker lifecycle on top of on_the_way → completed:
            // the specialist marks arrival on-site, then begins the work itself.
            // work_started_at drives the in-app work timer. Status stays varchar,
            // so the new 'arrived' / 'in_progress' values need no enum change.
            $table->dateTime('arrived_at')->nullable()->after('started_at');
            $table->dateTime('work_started_at')->nullable()->after('arrived_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['arrived_at', 'work_started_at']);
        });
    }
};

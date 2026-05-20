<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('gender', 16)->nullable()->after('city');
            $table->string('area')->nullable()->after('city');
            $table->boolean('profile_completed')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['gender', 'area', 'profile_completed']);
        });
    }
};

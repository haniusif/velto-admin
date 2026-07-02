<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            // OTP-based auth (mirrors customers): no password column.
            $table->string('preferred_language', 2)->default('ar')->after('status');
            $table->timestamp('last_login_at')->nullable()->after('preferred_language');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn(['preferred_language', 'last_login_at']);
        });
    }
};

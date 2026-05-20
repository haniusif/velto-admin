<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 32)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('city')->default('Riyadh');
            $table->string('preferred_language', 8)->default('ar');
            $table->string('status', 32)->default('active');
            $table->text('notes')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

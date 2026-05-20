<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 32)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('national_id', 32)->nullable();
            $table->string('city')->default('Riyadh');
            $table->string('status', 32)->default('active');
            $table->date('hire_date')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};

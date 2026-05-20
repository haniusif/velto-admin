<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // terms | privacy | refund
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->longText('body')->nullable();
            $table->longText('body_ar')->nullable();
            $table->string('version', 16)->default('1.0');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_pages');
    }
};

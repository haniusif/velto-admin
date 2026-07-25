<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            // FCM registration token — unique across the fleet (a token belongs
            // to one install; on reinstall/reassign it re-registers to the new customer).
            $table->string('fcm_token', 512)->unique();
            $table->string('platform', 16)->nullable();   // android | ios | web
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_devices');
    }
};

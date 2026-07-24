<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            // FCM registration token — unique across the fleet (a token belongs
            // to one install; on reinstall/reassign it re-registers to the new worker).
            $table->string('fcm_token', 512)->unique();
            $table->string('platform', 16)->nullable();   // android | ios | web
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('worker_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_devices');
    }
};

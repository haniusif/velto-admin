<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('wash_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('time_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('wallet_transaction_id')->nullable()->constrained()->nullOnDelete();

            // pending | confirmed | on_the_way | completed | cancelled
            $table->string('status', 32)->default('confirmed');
            $table->dateTime('scheduled_at');

            // Location (free point + optional resolved area/zone).
            $table->string('address_label')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();

            // Snapshots — preserve what was booked even if catalog/vehicle changes later.
            $table->string('service_name')->nullable();
            $table->string('service_name_ar')->nullable();
            $table->string('vehicle_label')->nullable();
            $table->json('add_ons')->nullable(); // [{id,name,name_ar,extra_price}]

            // Pricing (SAR).
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('addons_total', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);

            // Payment.
            $table->string('payment_method', 16)->default('wallet'); // wallet | card | apple_pay
            $table->string('payment_status', 16)->default('pending'); // pending | paid | refunded

            $table->text('notes')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prepaid multi-visit plans: a customer buys N visits of one wash package for
 * one vehicle, valid for a fixed window. Unused visits are forfeited at
 * expiry — there is no carry-over ledger by design.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wash_package_id')->constrained()->restrictOnDelete();

            // A plan is locked to the vehicle chosen at purchase. Kept nullable
            // so deleting a car doesn't destroy the purchase record; bookings
            // are refused once it's gone.
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedInteger('visits_total');
            $table->unsignedInteger('visits_used')->default(0);

            $table->decimal('price_paid', 10, 2);
            $table->string('payment_method', 20);           // wallet | card | apple_pay
            $table->string('payment_status', 20)->default('pending');
            $table->string('status', 20)->default('pending'); // pending | active | expired | cancelled

            // Set on activation, not purchase: a card plan only starts counting
            // down once the payment is actually captured.
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index('expires_at');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('customer_package_id')
                ->nullable()
                ->after('wash_package_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreignId('customer_package_id')
                ->nullable()
                ->after('appointment_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_package_id');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_package_id');
        });

        Schema::dropIfExists('customer_packages');
    }
};

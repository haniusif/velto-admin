<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a customer be deleted without destroying the financial record.
 *
 * App Review requires an in-app account deletion (Guideline 5.1.1(v)), and
 * every customer_id foreign key cascaded — so deleting an account would have
 * taken the payment history with it. A payment row stands on its own: amount,
 * currency, gateway references, status and timestamps. Detaching it keeps the
 * books intact while the person's identity goes away, which is what Apple asks
 * for and what invoice-retention rules need.
 *
 * Everything genuinely personal — vehicles, addresses, appointments, wallet,
 * devices, notifications, reviews — still cascades.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite (used by the test suite) cannot alter a foreign key in place.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->change();
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Rows detached while this was live have no customer to restore, so they
        // would block a NOT NULL column. Drop them rather than fail the rollback.
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        \DB::table('payment_transactions')->whereNull('customer_id')->delete();

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable(false)->change();
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();

            $table->string('gateway', 16)->default('arb');
            $table->string('action', 16)->default('purchase'); // purchase | refund
            // pending | captured | failed | refunded
            $table->string('status', 16)->default('pending');

            $table->decimal('amount', 10, 2);
            $table->string('currency', 8)->default('SAR');

            // ARB references — trackId is ours (unique), the rest come back from PG.
            $table->string('track_id', 64)->unique();
            $table->string('payment_id', 64)->nullable()->index();
            $table->string('trans_id', 64)->nullable()->index();
            $table->string('ref', 64)->nullable();

            $table->string('result_code', 32)->nullable();
            $table->string('error_code', 32)->nullable();
            $table->string('error_text')->nullable();

            $table->json('response_payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

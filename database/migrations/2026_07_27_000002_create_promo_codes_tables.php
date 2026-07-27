<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Promotional discounts on a booking's total.
 *
 * Redemptions are their own table rather than a counter on promo_codes: usage
 * limits have to be enforced per customer as well as overall, and a bare
 * counter cannot answer "has this person used it before". It also gives
 * finance a record of what each code actually cost.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();

            // Stored uppercase; lookups normalise, so "save10" and "SAVE10"
            // are the same code to a customer.
            $table->string('code', 40)->unique();
            $table->string('description')->nullable();
            $table->string('description_ar')->nullable();

            $table->string('type', 10);                       // percent | fixed
            $table->decimal('value', 10, 2);                  // 15 = 15% or 15 SAR

            // Percent codes without a ceiling are how you accidentally give
            // away a 40 SAR detail for nothing.
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->decimal('min_order_total', 10, 2)->default(0);

            $table->unsignedInteger('usage_limit')->nullable();      // total, null = unlimited
            $table->unsignedInteger('per_customer_limit')->default(1);
            $table->unsignedInteger('used_count')->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'expires_at']);
        });

        Schema::create('promo_code_redemptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount', 10, 2);   // what the discount actually cost

            $table->timestamps();

            $table->index(['promo_code_id', 'customer_id']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('promo_code_id')->nullable()->after('customer_package_id')
                ->constrained()->nullOnDelete();
            $table->decimal('discount_total', 10, 2)->default(0)->after('addons_total');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn('discount_total');
        });

        Schema::dropIfExists('promo_code_redemptions');
        Schema::dropIfExists('promo_codes');
    }
};

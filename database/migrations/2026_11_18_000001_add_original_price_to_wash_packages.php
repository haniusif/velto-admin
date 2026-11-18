<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wash_packages', function (Blueprint $table) {
            // Pre-discount ("compare at") price. Shown struck through in the
            // app next to the live price. Nullable: no value = no strikethrough.
            $table->decimal('original_price', 10, 2)->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('wash_packages', function (Blueprint $table) {
            $table->dropColumn('original_price');
        });
    }
};

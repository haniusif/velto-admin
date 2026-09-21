<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tickets a customer opens from the app's Help Center: complaints,
 * suggestions, and general questions. Until now the only channels were
 * WhatsApp and email, which left nothing for the team to track or answer
 * from the panel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            // The booking a complaint is about, when there is one.
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type', 20);   // complaint | suggestion | inquiry | other
            $table->string('subject', 160);
            $table->text('message');

            $table->string('status', 20)->default('open'); // open | in_progress | resolved | closed
            $table->text('admin_reply')->nullable();
            $table->timestamp('replied_at')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};

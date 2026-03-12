<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained();
            $table->foreignId('ticket_type_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ticket_number', 30)->unique();
            $table->string('qr_code_secret', 64)->unique();
            // Attendee details
            $table->string('attendee_name', 100);
            $table->string('attendee_email', 150)->nullable();
            $table->string('attendee_phone', 20)->nullable();
            // Check-in
            $table->enum('status', ['active', 'used', 'cancelled', 'transferred', 'refunded'])->default('active');
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('check_in_device', 100)->nullable();
            $table->text('check_in_notes')->nullable();
            // Transfer
            $table->foreignId('transferred_from')->nullable()->constrained('tickets')->nullOnDelete();
            $table->timestamp('transferred_at')->nullable();
            // Flags
            $table->boolean('is_complimentary')->default(false);
            $table->timestamp('pdf_generated_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('ticket_number');
            $table->index('qr_code_secret');
            $table->index(['event_id', 'status']);
            $table->index('attendee_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

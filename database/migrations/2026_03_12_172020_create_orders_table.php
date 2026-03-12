<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 20)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_id')->constrained();
            $table->foreignId('promo_code_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', [
                'pending', 'processing', 'paid', 'failed',
                'cancelled', 'refunded', 'partially_refunded',
            ])->default('pending');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('organizer_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('currency', 3)->default('KES');
            $table->enum('payment_method', [
                'mpesa', 'card', 'bank_transfer', 'free', 'complimentary',
            ])->nullable();
            $table->string('payment_gateway', 50)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->string('mpesa_receipt_number', 50)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            // Buyer snapshot
            $table->string('buyer_name', 100);
            $table->string('buyer_email', 150);
            $table->string('buyer_phone', 20);
            // Meta
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('order_number');
            $table->index(['user_id', 'status']);
            $table->index(['event_id', 'status']);
            $table->index('payment_reference');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

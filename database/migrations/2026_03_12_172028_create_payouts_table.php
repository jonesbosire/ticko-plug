<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users');
            $table->string('payout_number', 20)->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('platform_fee_deducted', 15, 2)->default(0);
            $table->decimal('refunds_deducted', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->enum('status', ['pending', 'processing', 'paid', 'failed', 'on_hold'])->default('pending');
            $table->enum('payment_method', ['mpesa', 'bank_transfer'])->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('line_items')->nullable();
            $table->timestamps();

            $table->index(['organizer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('gateway', ['daraja_stk', 'daraja_c2b', 'flutterwave', 'pesapal', 'manual']);
            $table->string('gateway_transaction_id', 100)->nullable();
            $table->string('gateway_reference', 100)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('KES');
            $table->enum('status', ['initiated', 'pending', 'completed', 'failed', 'reversed'])->default('initiated');
            $table->json('gateway_response')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mpesa_receipt', 50)->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

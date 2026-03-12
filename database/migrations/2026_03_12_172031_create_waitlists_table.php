<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waitlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email', 150);
            $table->string('phone', 20)->nullable();
            $table->string('name', 100);
            $table->tinyInteger('quantity_requested')->default(1);
            $table->unsignedInteger('position');
            $table->enum('status', ['waiting', 'notified', 'purchased', 'expired'])->default('waiting');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['ticket_type_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlists');
    }
};

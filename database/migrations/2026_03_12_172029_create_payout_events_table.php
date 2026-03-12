<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained();
            $table->unsignedInteger('tickets_sold');
            $table->decimal('gross_revenue', 12, 2);
            $table->decimal('platform_fee', 12, 2);
            $table->decimal('organizer_amount', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_events');
    }
};

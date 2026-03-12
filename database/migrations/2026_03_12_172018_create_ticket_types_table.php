<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('KES');
            $table->unsignedInteger('quantity_total');
            $table->unsignedInteger('quantity_sold')->default(0);
            $table->unsignedInteger('quantity_reserved')->default(0);
            $table->tinyInteger('min_per_order')->default(1);
            $table->tinyInteger('max_per_order')->default(10);
            $table->dateTime('sale_starts_at')->nullable();
            $table->dateTime('sale_ends_at')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->smallInteger('sort_order')->default(0);
            $table->string('color', 7)->nullable();
            $table->json('perks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'is_visible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};

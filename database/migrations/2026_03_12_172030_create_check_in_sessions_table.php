<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_in_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained();
            $table->foreignId('started_by')->constrained('users');
            $table->string('device_name', 100)->nullable();
            $table->string('device_token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('total_checked_in')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'is_active']);
            $table->index('device_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_in_sessions');
    }
};

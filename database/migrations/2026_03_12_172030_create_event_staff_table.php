<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invite_email', 150)->nullable();
            $table->enum('role', ['co_organizer', 'door_staff', 'coordinator']);
            $table->json('permissions')->nullable();
            $table->foreignId('invited_by')->constrained('users');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_staff');
    }
};

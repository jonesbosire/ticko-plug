<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('venue_id')->nullable()->constrained('venues')->nullOnDelete();
            $table->string('title', 200);
            $table->string('slug')->unique();
            $table->string('tagline', 300)->nullable();
            $table->longText('description');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->dateTime('doors_open_at')->nullable();
            $table->string('timezone', 50)->default('Africa/Nairobi');
            $table->enum('status', ['draft', 'published', 'cancelled', 'postponed', 'completed'])->default('draft');
            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('public');
            $table->boolean('is_online')->default(false);
            $table->string('online_event_url')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_rule')->nullable();
            $table->tinyInteger('min_age')->unsigned()->nullable();
            $table->string('dress_code', 100)->nullable();
            $table->json('tags')->nullable();
            $table->timestamp('featured_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->unsignedInteger('total_tickets_sold')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('platform_fee_override', 5, 2)->nullable();
            $table->boolean('organizer_absorbs_fee')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'start_datetime']);
            $table->index(['organizer_id', 'status']);
            $table->index('category_id');
            $table->index('featured_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

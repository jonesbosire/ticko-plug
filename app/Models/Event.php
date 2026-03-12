<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Event extends Model implements HasMedia
{
    use HasSlug, SoftDeletes, InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'organizer_id', 'category_id', 'venue_id', 'title', 'slug', 'tagline',
        'description', 'start_datetime', 'end_datetime', 'doors_open_at',
        'timezone', 'status', 'visibility', 'is_online', 'online_event_url',
        'is_recurring', 'recurrence_rule', 'min_age', 'dress_code', 'tags',
        'featured_at', 'cancelled_at', 'cancellation_reason',
        'total_tickets_sold', 'total_revenue', 'platform_fee_override',
        'organizer_absorbs_fee', 'created_by', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime'       => 'datetime',
            'end_datetime'         => 'datetime',
            'doors_open_at'        => 'datetime',
            'featured_at'          => 'datetime',
            'cancelled_at'         => 'datetime',
            'published_at'         => 'datetime',
            'status'               => EventStatus::class,
            'is_online'            => 'boolean',
            'is_recurring'         => 'boolean',
            'organizer_absorbs_fee'=> 'boolean',
            'tags'                 => 'array',
            'recurrence_rule'      => 'array',
            'total_revenue'        => 'decimal:2',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')->singleFile();
        $this->addMediaCollection('gallery');
    }

    // Accessors
    public function getBannerUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('banner') ?: null;
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_datetime->isFuture();
    }

    public function getIsPastAttribute(): bool
    {
        return $this->end_datetime->isPast();
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->status === EventStatus::Published
            && $this->start_datetime->isFuture()
            && $this->ticketTypes()->where('is_visible', true)->where('quantity_sold', '<', \DB::raw('quantity_total'))->exists();
    }

    public function getAvailableTicketsCountAttribute(): int
    {
        return $this->ticketTypes->sum(fn ($t) => max(0, $t->quantity_total - $t->quantity_sold - $t->quantity_reserved));
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', EventStatus::Published);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>', now());
    }

    public function scopeFeatured($query)
    {
        return $query->whereNotNull('featured_at')->orderBy('featured_at', 'desc');
    }

    // Relationships
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    public function eventStaff(): HasMany
    {
        return $this->hasMany(EventStaff::class);
    }

    public function checkInSessions(): HasMany
    {
        return $this->hasMany(CheckInSession::class);
    }

    public function waitlists(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(EventUpdate::class);
    }
}

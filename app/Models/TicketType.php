<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id', 'name', 'description', 'price', 'currency',
        'quantity_total', 'quantity_sold', 'quantity_reserved',
        'min_per_order', 'max_per_order', 'sale_starts_at', 'sale_ends_at',
        'is_visible', 'requires_approval', 'sort_order', 'color', 'perks',
    ];

    protected function casts(): array
    {
        return [
            'price'           => 'decimal:2',
            'sale_starts_at'  => 'datetime',
            'sale_ends_at'    => 'datetime',
            'is_visible'      => 'boolean',
            'requires_approval' => 'boolean',
            'perks'           => 'array',
        ];
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity_total - $this->quantity_sold - $this->quantity_reserved);
    }

    public function getIsFreeAttribute(): bool
    {
        return $this->price == 0;
    }

    public function getIsSoldOutAttribute(): bool
    {
        return $this->available_quantity <= 0;
    }

    public function getIsOnSaleAttribute(): bool
    {
        if ($this->is_sold_out) {
            return false;
        }
        $now = now();
        if ($this->sale_starts_at && $now->lt($this->sale_starts_at)) {
            return false;
        }
        if ($this->sale_ends_at && $now->gt($this->sale_ends_at)) {
            return false;
        }

        return true;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}

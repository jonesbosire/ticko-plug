<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    protected $fillable = [
        'event_id', 'code', 'type', 'discount_value', 'currency',
        'max_uses', 'used_count', 'min_order_amount',
        'applicable_ticket_types', 'valid_from', 'valid_until',
        'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'discount_value'           => 'decimal:2',
            'min_order_amount'         => 'decimal:2',
            'applicable_ticket_types'  => 'array',
            'valid_from'               => 'datetime',
            'valid_until'              => 'datetime',
            'is_active'                => 'boolean',
        ];
    }

    public function getIsValidAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        return match ($this->type) {
            'percentage'   => round($subtotal * ($this->discount_value / 100), 2),
            'fixed_amount' => min($this->discount_value, $subtotal),
            'free'         => $subtotal,
            default        => 0,
        };
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}

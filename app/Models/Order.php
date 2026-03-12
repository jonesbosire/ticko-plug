<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'order_number', 'user_id', 'event_id', 'promo_code_id', 'status',
        'subtotal', 'discount_amount', 'platform_fee', 'organizer_amount', 'total',
        'currency', 'payment_method', 'payment_gateway', 'payment_reference',
        'mpesa_receipt_number', 'paid_at', 'expires_at',
        'buyer_name', 'buyer_email', 'buyer_phone',
        'notes', 'ip_address', 'user_agent',
        'refund_reason', 'refunded_at', 'refunded_by',
    ];

    protected $hidden = ['ip_address', 'user_agent'];

    protected function casts(): array
    {
        return [
            'status'        => OrderStatus::class,
            'subtotal'      => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'platform_fee'  => 'decimal:2',
            'organizer_amount' => 'decimal:2',
            'total'         => 'decimal:2',
            'paid_at'       => 'datetime',
            'expires_at'    => 'datetime',
            'refunded_at'   => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'payment_reference', 'paid_at', 'refunded_at'])->logOnlyDirty();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && $this->status === OrderStatus::Pending;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}

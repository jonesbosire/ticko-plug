<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payout extends Model
{
    protected $fillable = [
        'organizer_id', 'payout_number', 'period_start', 'period_end',
        'gross_amount', 'platform_fee_deducted', 'refunds_deducted', 'net_amount',
        'status', 'payment_method', 'payment_reference', 'paid_at',
        'processed_by', 'notes', 'line_items',
    ];

    protected function casts(): array
    {
        return [
            'status'                => PayoutStatus::class,
            'gross_amount'          => 'decimal:2',
            'platform_fee_deducted' => 'decimal:2',
            'refunds_deducted'      => 'decimal:2',
            'net_amount'            => 'decimal:2',
            'period_start'          => 'date',
            'period_end'            => 'date',
            'paid_at'               => 'datetime',
            'line_items'            => 'array',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function payoutEvents(): HasMany
    {
        return $this->hasMany(PayoutEvent::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutEvent extends Model
{
    protected $fillable = [
        'payout_id', 'event_id', 'tickets_sold',
        'gross_revenue', 'platform_fee', 'organizer_amount',
    ];

    protected function casts(): array
    {
        return [
            'gross_revenue'    => 'decimal:2',
            'platform_fee'     => 'decimal:2',
            'organizer_amount' => 'decimal:2',
        ];
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}

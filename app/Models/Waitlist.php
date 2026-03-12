<?php

namespace App\Models;

use App\Enums\WaitlistStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    protected $fillable = [
        'event_id', 'ticket_type_id', 'user_id', 'email', 'phone', 'name',
        'quantity_requested', 'position', 'status', 'notified_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => WaitlistStatus::class,
            'notified_at'  => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

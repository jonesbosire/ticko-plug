<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id', 'order_item_id', 'event_id', 'ticket_type_id', 'user_id',
        'ticket_number', 'qr_code_secret',
        'attendee_name', 'attendee_email', 'attendee_phone',
        'status', 'checked_in_at', 'checked_in_by', 'check_in_device', 'check_in_notes',
        'transferred_from', 'transferred_at',
        'is_complimentary', 'pdf_generated_at', 'email_sent_at', 'sms_sent_at',
    ];

    protected $hidden = ['qr_code_secret'];

    protected function casts(): array
    {
        return [
            'status'           => TicketStatus::class,
            'checked_in_at'    => 'datetime',
            'transferred_at'   => 'datetime',
            'pdf_generated_at' => 'datetime',
            'email_sent_at'    => 'datetime',
            'sms_sent_at'      => 'datetime',
            'is_complimentary' => 'boolean',
        ];
    }

    public function getIsValidAttribute(): bool
    {
        return $this->status === TicketStatus::Active;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
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

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function transferredFromTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'transferred_from');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventUpdate extends Model
{
    protected $fillable = [
        'event_id', 'title', 'content', 'notify_attendees', 'notified_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'notify_attendees' => 'boolean',
            'notified_at'      => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

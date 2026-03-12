<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventStaff extends Model
{
    protected $fillable = [
        'event_id', 'user_id', 'invite_email', 'role',
        'permissions', 'invited_by', 'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions'  => 'array',
            'accepted_at'  => 'datetime',
        ];
    }

    public function getHasAcceptedAttribute(): bool
    {
        return $this->accepted_at !== null;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}

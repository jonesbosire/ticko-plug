<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckInSession extends Model
{
    protected $fillable = [
        'event_id', 'started_by', 'device_name', 'device_token',
        'is_active', 'total_checked_in', 'started_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }
}

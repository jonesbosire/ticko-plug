<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'gateway', 'gateway_transaction_id', 'gateway_reference',
        'amount', 'currency', 'status', 'gateway_response',
        'phone', 'mpesa_receipt',
        'initiated_at', 'completed_at', 'failed_at', 'failure_reason',
    ];

    protected $hidden = ['gateway_response'];

    protected function casts(): array
    {
        return [
            'gateway'          => PaymentGateway::class,
            'amount'           => 'decimal:2',
            'gateway_response' => 'array',
            'initiated_at'     => 'datetime',
            'completed_at'     => 'datetime',
            'failed_at'        => 'datetime',
        ];
    }

    public function getIsSuccessfulAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

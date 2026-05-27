<?php

namespace App\Models;

use App\Enums\ConversionPlatform;
use App\Enums\ConversionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'platform',
        'event',
        'value',
        'currency',
        'status',
        'reason',
        'url',
        'request_payload',
        'response_payload',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'platform' => ConversionPlatform::class,
            'status' => ConversionStatus::class,
            'value' => 'decimal:2',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

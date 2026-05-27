<?php

namespace App\Models;

use App\Enums\StorageUnitStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'order_id',
        'type',
        'stock',
        'credentials',
        'delivery_data',
        'status',
        'reserved_at',
        'delivered_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'credentials' => 'encrypted',
            'delivery_data' => 'encrypted',
            'status' => StorageUnitStatus::class,
            'reserved_at' => 'datetime',
            'delivered_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'status',
        'form_data',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'status' => DeliveryStatus::class,
            'form_data' => 'array',
        ];
    }

    /**
     * Per-field order details for display — one entry per chosen option, so the
     * confirmation page, account page, email and Telegram message can render
     * each selection on its own line. Falls back to the compact summary for
     * legacy orders that only stored an 'option' string.
     *
     * @return list<array{label: ?string, value: string}>
     */
    public function detailLines(): array
    {
        $data = is_array($this->form_data) ? $this->form_data : [];
        $lines = [];

        foreach ($data as $key => $value) {
            if ($key === 'option' || is_array($value) || blank($value)) {
                continue;
            }

            $lines[] = ['label' => (string) $key, 'value' => (string) $value];
        }

        if ($lines === [] && filled($data['option'] ?? null)) {
            $lines[] = ['label' => null, 'value' => (string) $data['option']];
        }

        return $lines;
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

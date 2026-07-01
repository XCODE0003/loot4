<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Services\Notifications\OrderNotifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Order extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_number',
        'user_id',
        'coupon_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'ip',
        'country',
        'state',
        'town',
        'address',
        'postal_code',
        'delivery_method',
        'delivery_fee',
        'status',
        'payment_status',
        'delivery_status',
        'currency',
        'subtotal',
        'discount',
        'total',
        'discount_code',
        'source',
        'medium',
        'campaign',
        'content',
        'term',
        'gclid',
        'fbclid',
        'ttclid',
        'landing_page',
        'first_visit_at',
        'abandoned_reminded_at',
        'form_data',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'delivery_status' => DeliveryStatus::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'first_visit_at' => 'datetime',
            'abandoned_reminded_at' => 'datetime',
            'form_data' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (blank($order->order_number)) {
                $order->order_number = strtoupper(Str::random(10));
            }
        });

        // Email the customer when an order transitions to "delivered".
        static::updated(function (Order $order): void {
            if ($order->wasChanged('delivery_status') && $order->delivery_status === DeliveryStatus::Delivered) {
                app(OrderNotifier::class)->delivered($order);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_status', 'delivery_status', 'total', 'discount'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Coupon, $this> */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasMany<StorageUnit, $this> */
    public function storageUnits(): HasMany
    {
        return $this->hasMany(StorageUnit::class);
    }

    /** @return HasMany<ConversionLog, $this> */
    public function conversionLogs(): HasMany
    {
        return $this->hasMany(ConversionLog::class);
    }
}

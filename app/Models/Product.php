<?php

namespace App\Models;

use App\Enums\OptionsLayout;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Concerns\ServesOptimizedMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, ServesOptimizedMedia;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'price', 'compare_price', 'status'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected static function booted(): void
    {
        // New products go to the end of the list (highest sort_order) unless an
        // explicit position was set, so they don't jump to the top.
        static::creating(function (Product $product): void {
            if (empty($product->sort_order)) {
                $product->sort_order = (int) static::max('sort_order') + 1;
            }
        });
    }

    protected $fillable = [
        'game_id',
        'currency_id',
        'name',
        'slug',
        'type',
        'options_layout',
        'short_description',
        'description',
        'html_description',
        'price',
        'compare_price',
        'status',
        'auto_delivery',
        'express_delivery',
        'express_fee',
        'express_time',
        'delivery_options',
        'delivery_instructions',
        'allowed_payment_methods',
        'visibility',
        'featured',
        'meta_title',
        'meta_description',
        'og_image',
        'sort_order',
        'filter_values',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'options_layout' => OptionsLayout::class,
            'status' => ProductStatus::class,
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'auto_delivery' => 'boolean',
            'express_delivery' => 'boolean',
            'express_fee' => 'decimal:2',
            'delivery_options' => 'array',
            'visibility' => 'boolean',
            'featured' => 'boolean',
            'allowed_payment_methods' => 'array',
            'filter_values' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Normalized delivery choices shown on checkout, as a clean list of
     * ['label' => string, 'price' => float]. Blank rows are dropped and prices
     * coerced to non-negative floats. A price of 0 means free.
     *
     * @return list<array{label: string, price: float}>
     */
    public function deliveryOptions(): array
    {
        $options = is_array($this->delivery_options) ? $this->delivery_options : [];

        return array_values(array_filter(array_map(function ($opt): ?array {
            if (! is_array($opt)) {
                return null;
            }

            $label = trim((string) ($opt['label'] ?? ''));
            if ($label === '') {
                return null;
            }

            return ['label' => $label, 'price' => max(0.0, round((float) ($opt['price'] ?? 0), 2))];
        }, $options)));
    }

    /** @return BelongsTo<Game, $this> */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /** @return BelongsTo<Currency, $this> */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /** @return HasMany<ProductForm, $this> */
    public function forms(): HasMany
    {
        return $this->hasMany(ProductForm::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<StorageUnit, $this> */
    public function storageUnits(): HasMany
    {
        return $this->hasMany(StorageUnit::class);
    }

    /**
     * Total available stock across the product's storage units.
     */
    public function availableStock(): int
    {
        return (int) $this->storageUnits()
            ->where('status', 'available')
            ->sum('stock');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main')->singleFile()->useDisk('public');
        $this->addMediaCollection('gallery')->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Resized WebP served to the storefront (originals are large uploads).
        // Generated synchronously so new uploads are immediately optimized.
        $this->addMediaConversion('web')
            ->format('webp')
            ->width(900)
            ->quality(80)
            ->performOnCollections('main', 'gallery')
            ->nonQueued();
    }
}

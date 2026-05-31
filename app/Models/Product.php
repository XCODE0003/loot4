<?php

namespace App\Models;

use App\Enums\OptionsLayout;
use App\Enums\ProductStatus;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'price', 'compare_price', 'status'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
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
            'visibility' => 'boolean',
            'featured' => 'boolean',
            'allowed_payment_methods' => 'array',
            'filter_values' => 'array',
            'sort_order' => 'integer',
        ];
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
}

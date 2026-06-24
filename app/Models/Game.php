<?php

namespace App\Models;

use App\Enums\GameStatus;
use App\Models\Concerns\ServesOptimizedMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Game extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, ServesOptimizedMedia;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'tags',
        'status',
        'sort_order',
        'meta_title',
        'meta_description',
        'landing_settings',
        'game_filters',
        'guarantees',
        'show_search',
    ];

    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
            'tags' => 'array',
            'landing_settings' => 'array',
            'game_filters' => 'array',
            'guarantees' => 'array',
            'show_search' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile()->useDisk('public');
        $this->addMediaCollection('icon')->singleFile()->useDisk('public');
        $this->addMediaCollection('discover_image')->singleFile()->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Resized WebP for the storefront. The small icon stays as-is.
        $this->addMediaConversion('web')
            ->format('webp')
            ->width(800)
            ->quality(80)
            ->performOnCollections('image', 'discover_image')
            ->nonQueued();
    }
}

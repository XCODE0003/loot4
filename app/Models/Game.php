<?php

namespace App\Models;

use App\Enums\GameStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Game extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

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
    ];

    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
            'tags' => 'array',
            'landing_settings' => 'array',
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
        $this->addMediaCollection('image')->singleFile();
        $this->addMediaCollection('discover_image')->singleFile();
    }
}

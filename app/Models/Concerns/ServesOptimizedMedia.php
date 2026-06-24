<?php

namespace App\Models\Concerns;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Serve the resized/WebP conversion of a media item when it has been generated,
 * falling back to the original otherwise — so images never break in the window
 * between a deploy and `media-library:regenerate` finishing.
 */
trait ServesOptimizedMedia
{
    public function optimizedMediaUrl(string $collection, string $conversion = 'web'): ?string
    {
        $media = $this->getFirstMedia($collection);

        if (! $media instanceof Media) {
            return null;
        }

        return $media->hasGeneratedConversion($conversion)
            ? $media->getUrl($conversion)
            : $media->getUrl();
    }
}

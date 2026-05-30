<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * How a product option group affects the line price.
 */
enum PricingMode: string implements HasLabel
{
    /** Each selected option adds its price to the total (the default). */
    case Addon = 'addon';

    /** The customer picks one option whose price becomes the product price. */
    case Absolute = 'absolute';

    public function getLabel(): string
    {
        return match ($this) {
            self::Addon => 'Add-on (adds to the total)',
            self::Absolute => 'Price selector (customer picks the price)',
        };
    }
}

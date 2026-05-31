<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * How the product option groups are presented on the product page.
 */
enum OptionsLayout: string implements HasLabel
{
    /** All option groups shown together on one page (the default). */
    case Single = 'single';

    /** One group per step with a progress indicator and Back/Next navigation. */
    case Steps = 'steps';

    public function getLabel(): string
    {
        return match ($this) {
            self::Single => 'All on one page',
            self::Steps => 'Step by step',
        };
    }
}

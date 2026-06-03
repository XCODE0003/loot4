<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ConversionStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
    // The client decided not to fire the pixel (already sent for this order,
    // or the visitor has not accepted marketing cookies). Logged for debugging.
    case Skipped = 'skipped';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Sent => 'success',
            self::Failed => 'danger',
            self::Skipped => 'gray',
        };
    }
}

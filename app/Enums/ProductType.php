<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProductType: string implements HasLabel
{
    case Boosting = 'boosting';
    case Accounts = 'accounts';
    case Items = 'items';
    case Currency = 'currency';
    case Services = 'services';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }
}

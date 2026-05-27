<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FieldType: string implements HasLabel
{
    case Select = 'select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case Text = 'text';
    case Number = 'number';
    case Textarea = 'textarea';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Whether this field type carries a predefined set of options.
     */
    public function hasOptions(): bool
    {
        return in_array($this, [self::Select, self::Radio, self::Checkbox], true);
    }
}

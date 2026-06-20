<?php

namespace App\Models;

use App\Enums\FieldType;
use App\Enums\PricingMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_form_id',
        'label',
        'key',
        'type',
        'pricing_mode',
        'options',
        'options_columns',
        'step',
        'required',
        'extra_price',
        'tooltip',
        'conditional_logic',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => FieldType::class,
            'pricing_mode' => PricingMode::class,
            'options' => 'array',
            'options_columns' => 'integer',
            'step' => 'integer',
            'required' => 'boolean',
            'extra_price' => 'decimal:2',
            'conditional_logic' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<ProductForm, $this> */
    public function form(): BelongsTo
    {
        return $this->belongsTo(ProductForm::class, 'product_form_id');
    }
}

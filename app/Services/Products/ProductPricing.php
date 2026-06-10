<?php

namespace App\Services\Products;

use App\Enums\PricingMode;
use App\Models\Product;
use App\Models\ProductFormField;
use Illuminate\Support\Collection;

/**
 * Single source of truth for product pricing built from dynamic option groups.
 *
 * Used by both the storefront (display "from" price) and the checkout
 * (authoritative line-price recompute — the client price is never trusted).
 *
 * Selections are keyed by field key, e.g. ['amount' => '25m', 'addons' => ['ram', 'ssd']].
 *
 * @phpstan-type Selections array<string, string|list<string>>
 */
class ProductPricing
{
    /**
     * Max length kept from a free-form input value (text/number/textarea) when
     * pricing, summarising and storing it — defends against oversized payloads.
     */
    public const INPUT_MAX_LENGTH = 120;

    /**
     * The lowest price a customer could pay — the "from" price on cards and the
     * product page. With a price-selector (absolute) group this is the cheapest
     * selectable option; otherwise the product's own base price.
     */
    public function fromPrice(Product $product): float
    {
        $absolute = $this->fields($product)
            ->filter(fn (ProductFormField $f): bool => $f->pricing_mode === PricingMode::Absolute);

        if ($absolute->isEmpty()) {
            return round((float) $product->price, 2);
        }

        // Cheapest achievable base: the minimum option of each absolute group
        // (there is normally exactly one such group).
        $base = $absolute->sum(fn (ProductFormField $f): float => (float) (collect($f->options)
            ->map(fn ($o): float => (float) ($o['extra_price'] ?? 0))
            ->min() ?? 0.0));

        return round((float) $base, 2);
    }

    /**
     * Authoritative line unit price for a set of selections.
     *
     * @param  Selections  $selections
     */
    public function linePrice(Product $product, array $selections): float
    {
        // Start from the "from" price so an unselected variant group falls back to
        // its cheapest option (matching what the storefront shows), not the raw
        // base price. A selected absolute group then replaces this entirely.
        $base = $this->fromPrice($product);
        $hasAbsolute = false;
        $addons = 0.0;

        foreach ($this->fields($product) as $field) {
            $picked = $this->pickedOptions($field, $selections[$field->key] ?? null);

            if ($field->pricing_mode === PricingMode::Absolute) {
                if ($picked === []) {
                    continue;
                }
                // The first selected absolute group replaces the base price.
                if (! $hasAbsolute) {
                    $base = 0.0;
                    $hasAbsolute = true;
                }
                $base += $this->sumPrices($picked);

                continue;
            }

            $addons += $this->sumPrices($picked);
        }

        // Free-form input fields add their base extra price once filled.
        foreach ($this->inputFields($product) as $field) {
            if ($this->inputValue($field, $selections[$field->key] ?? null) !== '') {
                $addons += (float) ($field->extra_price ?? 0);
            }
        }

        return round($base + $addons, 2);
    }

    /**
     * Human-readable, server-authoritative summary of the selected options,
     * e.g. "$25M GTA Cash · Max Stats". Stored on the order item.
     *
     * @param  Selections  $selections
     */
    public function summary(Product $product, array $selections): string
    {
        $labels = [];

        foreach ($this->fields($product) as $field) {
            foreach ($this->pickedOptions($field, $selections[$field->key] ?? null) as $option) {
                $label = trim((string) ($option['label'] ?? $option['value'] ?? ''));
                if ($label !== '') {
                    $labels[] = $label;
                }
            }
        }

        // Free-form input fields contribute "Label: typed value".
        foreach ($this->inputFields($product) as $field) {
            $value = $this->inputValue($field, $selections[$field->key] ?? null);
            if ($value !== '') {
                $labels[] = trim((string) $field->label).': '.$value;
            }
        }

        return implode(' · ', $labels);
    }

    /**
     * Whether a required option group has no valid selection. Such a line should
     * be rejected rather than silently charged the fallback base price.
     *
     * @param  Selections  $selections
     */
    public function missingRequiredSelection(Product $product, array $selections): bool
    {
        foreach ($this->fields($product) as $field) {
            if (! $field->required) {
                continue;
            }
            if ($this->pickedOptions($field, $selections[$field->key] ?? null) === []) {
                return true;
            }
        }

        foreach ($this->inputFields($product) as $field) {
            if ($field->required && $this->inputValue($field, $selections[$field->key] ?? null) === '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Active, option-bearing form fields for the product, in display order.
     *
     * @return Collection<int, ProductFormField>
     */
    private function fields(Product $product): Collection
    {
        return $product->forms
            ->filter(fn ($form): bool => (bool) ($form->is_active ?? true))
            ->flatMap(fn ($form) => $form->fields ?? collect())
            ->filter(fn (ProductFormField $f): bool => $f->type->hasOptions() && filled($f->options))
            ->sortBy('sort_order')
            ->values();
    }

    /**
     * Active, free-form input fields (text/number/textarea) for the product, in
     * display order. These carry no options; the customer types a value.
     *
     * @return Collection<int, ProductFormField>
     */
    private function inputFields(Product $product): Collection
    {
        return $product->forms
            ->filter(fn ($form): bool => (bool) ($form->is_active ?? true))
            ->flatMap(fn ($form) => $form->fields ?? collect())
            ->filter(fn (ProductFormField $f): bool => $f->type->isInput())
            ->sortBy('sort_order')
            ->values();
    }

    /**
     * The trimmed, length-capped string a customer typed into an input field.
     * Arrays (a tampered payload) and blanks collapse to ''.
     *
     * @param  string|list<string>|null  $value
     */
    private function inputValue(ProductFormField $field, string|array|null $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return mb_substr(trim($value), 0, self::INPUT_MAX_LENGTH);
    }

    /**
     * Resolve the selected option rows for a field, ignoring values that no
     * longer exist (defends against stale carts and tampering).
     *
     * @param  string|list<string>|null  $value
     * @return list<array<string, mixed>>
     */
    private function pickedOptions(ProductFormField $field, string|array|null $value): array
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }

        $wanted = array_map('strval', is_array($value) ? $value : [$value]);
        $options = is_array($field->options) ? $field->options : [];

        return array_values(array_filter(
            $options,
            fn ($o): bool => in_array((string) ($o['value'] ?? ''), $wanted, true),
        ));
    }

    /**
     * @param  list<array<string, mixed>>  $options
     */
    private function sumPrices(array $options): float
    {
        return array_sum(array_map(fn ($o): float => (float) ($o['extra_price'] ?? 0), $options));
    }
}

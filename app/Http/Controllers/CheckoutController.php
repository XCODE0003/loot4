<?php

namespace App\Http\Controllers;

use App\Enums\CouponType;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\Attribution\AttributionResolver;
use App\Services\Conversions\ConversionEligibility;
use App\Services\Geo\IpCountry;
use App\Services\Notifications\OrderNotifier;
use App\Services\Payments\IceNoxGateway;
use App\Services\Payments\PaymentException;
use App\Services\Products\ProductPricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CheckoutController extends Controller
{
    /**
     * Validate a promocode for the cart drawer (read-only, JSON).
     */
    public function coupon(Request $request): JsonResponse
    {
        $code = trim((string) $request->query('code'));
        $subtotal = (float) $request->query('subtotal', 0);

        $coupon = Coupon::query()->where('code', $code)->where('is_active', true)->first();

        $error = match (true) {
            ! $coupon => 'Invalid promocode',
            $coupon->expires_at && $coupon->expires_at->isPast() => 'Promocode expired',
            $coupon->starts_at && $coupon->starts_at->isFuture() => 'Promocode not active yet',
            $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit => 'Usage limit reached',
            $coupon->min_order_amount && $subtotal < (float) $coupon->min_order_amount => 'Minimum order '.number_format((float) $coupon->min_order_amount, 2),
            default => null,
        };

        if ($error) {
            return response()->json(['valid' => false, 'message' => $error]);
        }

        return response()->json([
            'valid' => true,
            'code' => $coupon->code,
            'type' => $coupon->type->value,
            'value' => (float) $coupon->value,
        ]);
    }

    public function index(): Response
    {
        return Inertia::render('loot4/Checkout', [
            'expressFee' => (float) config('checkout.express_delivery_fee'),
        ]);
    }

    /**
     * Place an order from the cart. Prices are recomputed server-side; payment is
     * not processed yet (order is created as pending).
     */
    public function store(Request $request, IceNoxGateway $gateway, ProductPricing $pricing): HttpResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            // Billing details. State is nullable — many countries have no regions.
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'country' => ['required', 'string', 'max:2'],
            'state' => ['nullable', 'string', 'max:120'],
            'town' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:40'],
            'delivery' => ['nullable', 'string', 'in:standard,express'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.slug' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.option' => ['nullable', 'string'],
            'items.*.selections' => ['nullable', 'array'],
            'coupon' => ['nullable', 'string'],
            'method' => ['nullable', 'string', 'in:'.implode(',', self::PAYMENT_METHODS)],
            // Attribution is sanitized leniently by AttributionResolver — bad
            // tracking data must never block a checkout, so no strict rules here.
            'attribution' => ['nullable', 'array'],
        ]);

        $method = $data['method'] ?? self::PAYMENT_METHODS[0];

        // Delivery fee is server-authoritative — the client only names the choice.
        $deliveryMethod = ($data['delivery'] ?? 'standard') === 'express' ? 'express' : 'standard';
        $deliveryFee = $deliveryMethod === 'express' ? (float) config('checkout.express_delivery_fee') : 0.0;

        $lines = [];
        $subtotal = 0.0;
        $incomplete = false;

        foreach ($data['items'] as $row) {
            $product = Product::query()
                ->where('slug', $row['slug'])
                ->where('status', ProductStatus::Active->value)
                ->first();

            if (! $product) {
                continue;
            }

            $selections = $this->normalizeSelections(is_array($row['selections'] ?? null) ? $row['selections'] : []);

            // A required option group with no valid choice means the cart line is
            // incomplete — block checkout rather than charge the fallback price.
            if ($pricing->missingRequiredSelection($product, $selections)) {
                $incomplete = true;

                continue;
            }

            $qty = (int) $row['qty'];
            $price = $pricing->linePrice($product, $selections);
            $summary = $pricing->summary($product, $selections);
            $subtotal += $price * $qty;

            $lines[] = [
                'product' => $product,
                'qty' => $qty,
                'price' => $price,
                // Prefer the server-built summary; fall back to the client label for
                // plain products that carry no structured selections.
                'option' => $summary !== '' ? $summary : ($row['option'] ?? null),
                // Per-field [label => value] map for the order details display.
                'breakdown' => $pricing->breakdown($product, $selections),
            ];
        }

        if ($incomplete) {
            return back()->withErrors(['items' => 'Please choose all required options before checkout.']);
        }

        if ($lines === []) {
            return back()->withErrors(['items' => 'No valid items in the cart.']);
        }

        $coupon = $this->resolveCoupon($data['coupon'] ?? null, $subtotal);
        $discount = $this->discountFor($coupon, $subtotal);
        $total = max(0, $subtotal - $discount) + $deliveryFee;

        // Prefer the country the customer selected; fall back to IP geolocation.
        $country = $data['country'] ?? app(IpCountry::class)->lookup($request->ip());

        // Normalize the client-captured attribution (utm/gclid/fbclid/...) into
        // order columns; falls back to source=storefront when nothing was captured.
        $attribution = app(AttributionResolver::class)->resolve(
            is_array($data['attribution'] ?? null) ? $data['attribution'] : [],
            $request->getHost(),
        );

        $order = DB::transaction(function () use ($data, $lines, $subtotal, $discount, $total, $coupon, $request, $method, $country, $attribution, $deliveryMethod, $deliveryFee): Order {
            $order = Order::create([
                'user_id' => $request->user()?->id,
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'ip' => $request->ip(),
                'country' => $country,
                'state' => $data['state'] ?? null,
                'town' => $data['town'],
                'address' => $data['address'],
                'postal_code' => $data['postal_code'],
                'delivery_method' => $deliveryMethod,
                'delivery_fee' => $deliveryFee,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'delivery_status' => DeliveryStatus::Pending,
                'currency' => 'USD',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'coupon_id' => $coupon?->id,
                'discount_code' => $coupon?->code,
                ...$attribution,
            ]);

            foreach ($lines as $line) {
                // One key per chosen field (rendered line-by-line everywhere),
                // plus a compact 'option' summary for table columns.
                $formData = $line['breakdown'];
                if (filled($line['option'])) {
                    $formData['option'] = $line['option'];
                }

                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'quantity' => $line['qty'],
                    'price' => $line['price'],
                    'status' => DeliveryStatus::Pending,
                    'form_data' => $formData === [] ? null : $formData,
                ]);
            }

            $order->payments()->create([
                'method' => $method,
                'amount' => $total,
                'currency' => $order->currency,
                'status' => PaymentStatus::Pending,
            ]);

            $coupon?->increment('used_count');

            return $order;
        });

        // Free order (e.g. a 100% discount coupon) — nothing to charge, so skip the
        // gateway, mark it paid and go straight to the confirmation page.
        if ($total <= 0.0) {
            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Processing,
            ]);
            $order->payments()->update(['status' => PaymentStatus::Paid->value]);

            app(OrderNotifier::class)->paid($order);

            return redirect()->route('checkout.success', $order->order_number);
        }

        // Hand off to the IceNox hosted payment page when configured.
        if ($gateway->isConfigured()) {
            try {
                $result = $gateway->createPayment($order, $this->icenoxMethod($method));
                $order->payments()->update(['transaction_id' => $result['paymentid']]);

                return Inertia::location($result['url']);
            } catch (PaymentException $e) {
                app(OrderNotifier::class)->failed($order, $e->getMessage());

                return back()->withErrors(['payment' => $e->getMessage()]);
            }
        }

        // No gateway configured — record the order and treat it as placed.
        app(OrderNotifier::class)->paid($order);

        return redirect()->route('checkout.success', $order->order_number);
    }

    /**
     * Re-open the hosted payment page for an unpaid order — target of the signed
     * "complete payment" link in the abandoned-cart reminder email.
     */
    public function payAgain(Order $order, IceNoxGateway $gateway): HttpResponse
    {
        // Already paid — nothing to charge; show the confirmation page.
        if ($order->payment_status === PaymentStatus::Paid) {
            return redirect()->route('checkout.success', $order->order_number);
        }

        if ($gateway->isConfigured()) {
            try {
                $method = $this->icenoxMethod((string) ($order->payments()->first()?->method ?? self::PAYMENT_METHODS[0]));
                $result = $gateway->createPayment($order, $method);
                $order->payments()->update(['transaction_id' => $result['paymentid']]);

                return Inertia::location($result['url']);
            } catch (PaymentException $e) {
                return redirect()->route('checkout')->withErrors(['payment' => $e->getMessage()]);
            }
        }

        return redirect()->route('checkout');
    }

    /**
     * Supported IceNox payment-method identifiers (pay.icenox.com/docs).
     * The storefront sends these values directly; we just validate them.
     *
     * @var list<string>
     */
    private const PAYMENT_METHODS = [
        'stripe-cards',
        'stripe-apple-pay',
        'stripe-google-pay',
        'stripe-link',
        'stripe-klarna',
        'stripe-amazon-pay',
        'stripe-bancontact',
        'stripe-eps',
        'stripe-revolut-pay',
    ];

    /**
     * Map a storefront method choice to an IceNox payment-method identifier.
     */
    private function icenoxMethod(string $method): string
    {
        return in_array($method, self::PAYMENT_METHODS, true) ? $method : self::PAYMENT_METHODS[0];
    }

    /**
     * Payment status webhook (gateway notification_url / webhook_url). CSRF-exempt.
     *
     * Handles the Nox gateway payload shape ({ status: "PAID", code, txid, amount })
     * as well as legacy field names. When a webhook secret is configured the
     * X-Signature header is verified — base64(sha256(secret + raw_body)) — and
     * unsigned/forged calls are rejected.
     */
    public function webhook(Request $request): JsonResponse
    {
        $raw = $request->getContent();
        $payload = $request->all();
        Log::info('Payment webhook received', [
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'content_type' => $request->header('Content-Type'),
            'has_signature' => filled($request->header('x-callback-hash')) || filled($request->header('X-Signature')),
            'payload' => $payload,
            'raw' => $raw,
        ]);

        // IceNox signs callbacks with the x-callback-hash header (older shapes use X-Signature).
        $secret = config('services.icenox.webhook_secret');
        if (filled($secret)) {
            $expected = base64_encode(hash('sha256', $secret.$raw, true));
            $provided = (string) ($request->header('x-callback-hash') ?? $request->header('X-Signature') ?? '');

            if ($provided === '' || ! hash_equals($expected, $provided)) {
                Log::warning('Payment webhook signature mismatch', [
                    'reference' => $payload['orderid'] ?? $payload['reference'] ?? $payload['code'] ?? null,
                ]);

                return response()->json(['received' => false, 'error' => 'invalid signature'], 401);
            }
        }

        // IceNox sends: paymentid / gateway_transactionid, orderid, paymentstatus.
        // Older/Nox shapes use txid / code / status — accept all.
        $txid = $payload['paymentid'] ?? $payload['gateway_transactionid'] ?? $payload['txid'] ?? $payload['payment_id'] ?? $payload['id'] ?? null;
        $reference = $payload['orderid'] ?? $payload['reference'] ?? $payload['code'] ?? null;

        $statusRaw = strtolower((string) ($payload['paymentstatus'] ?? $payload['status'] ?? $payload['state'] ?? $payload['payment_status'] ?? ''));
        $isPaid = ($payload['success'] ?? null) === true
            || in_array($statusRaw, ['paid', 'pay', 'success', 'successful', 'completed', 'complete', 'captured', 'settled', 'approved', '1', 'true'], true);
        $isFailed = in_array($statusRaw, ['failed', 'fail', 'cancelled', 'canceled', 'declined', 'error', 'expired', 'refused', 'chargeback'], true);

        $order = $txid ? Payment::query()->where('transaction_id', $txid)->first()?->order : null;

        if (! $order && filled($reference)) {
            $order = Order::query()->where('order_number', $reference)->first();
        }

        if ($order && $isPaid && $order->payment_status !== PaymentStatus::Paid) {
            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Processing,
            ]);
            $order->payments()->update(['status' => PaymentStatus::Paid->value]);

            app(OrderNotifier::class)->paid($order);
        } elseif ($order && $isFailed && $order->payment_status === PaymentStatus::Pending) {
            app(OrderNotifier::class)->failed($order, $payload['message'] ?? $statusRaw);
        }

        return response()->json(['received' => true]);
    }

    public function success(Request $request, Order $order): Response
    {
        $order->load(['items', 'payments']);

        $method = (string) ($order->payments->first()?->method ?? 'card');
        $methodLabel = Str::of($method)->replaceFirst('stripe-', '')->headline()->toString();

        return Inertia::render('loot4/CheckoutSuccess', [
            'order' => [
                'number' => $order->order_number,
                'email' => $order->email,
                'name' => $order->user?->name,
                'date' => $order->created_at?->format('M j, Y'),
                'paymentMethod' => $methodLabel,
                'currency' => $order->currency,
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'total' => (float) $order->total,
                // Ad pixels allowed to fire for this order's traffic source
                // (null = no paid source detected → fire all configured pixels).
                'conversionPlatforms' => ConversionEligibility::for($order),
                'items' => $order->items->map(fn ($item): array => [
                    'id' => (string) $item->product_id,
                    'name' => $item->product_name,
                    // One entry per chosen option, rendered on its own line.
                    'lines' => $item->detailLines(),
                    'qty' => $item->quantity,
                    'price' => (float) $item->price,
                ])->all(),
            ],
            'authed' => $request->user() !== null,
        ]);
    }

    /**
     * Trim and length-cap free-form selection values before they are priced and
     * stored. Choice values are short identifiers; free-text fields are bounded
     * to ProductPricing::INPUT_MAX_LENGTH to reject oversized payloads.
     *
     * @param  array<string, mixed>  $selections
     * @return array<string, string|list<string>>
     */
    private function normalizeSelections(array $selections): array
    {
        $cap = fn (mixed $v): string => mb_substr(trim((string) $v), 0, ProductPricing::INPUT_MAX_LENGTH);

        $clean = [];
        foreach ($selections as $key => $value) {
            $clean[(string) $key] = is_array($value)
                ? array_values(array_map($cap, $value))
                : $cap($value);
        }

        return $clean;
    }

    private function resolveCoupon(?string $code, float $subtotal): ?Coupon
    {
        if (blank($code)) {
            return null;
        }

        $coupon = Coupon::query()->where('code', $code)->where('is_active', true)->first();

        if (! $coupon) {
            return null;
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return null;
        }
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return null;
        }
        if ($coupon->min_order_amount && $subtotal < (float) $coupon->min_order_amount) {
            return null;
        }

        return $coupon;
    }

    private function discountFor(?Coupon $coupon, float $subtotal): float
    {
        if (! $coupon) {
            return 0.0;
        }

        $discount = $coupon->type === CouponType::Percentage
            ? $subtotal * (float) $coupon->value / 100
            : (float) $coupon->value;

        return round(min($discount, $subtotal), 2);
    }
}

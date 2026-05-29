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
use App\Services\Notifications\OrderNotifier;
use App\Services\Payments\IceNoxGateway;
use App\Services\Payments\PaymentException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        return Inertia::render('loot4/Checkout');
    }

    /**
     * Place an order from the cart. Prices are recomputed server-side; payment is
     * not processed yet (order is created as pending).
     */
    public function store(Request $request, IceNoxGateway $gateway): HttpResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.slug' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.option' => ['nullable', 'string'],
            'coupon' => ['nullable', 'string'],
            'method' => ['nullable', 'string', 'in:'.implode(',', self::PAYMENT_METHODS)],
        ]);

        $method = $data['method'] ?? self::PAYMENT_METHODS[0];

        $lines = [];
        $subtotal = 0.0;

        foreach ($data['items'] as $row) {
            $product = Product::query()
                ->where('slug', $row['slug'])
                ->where('status', ProductStatus::Active->value)
                ->first();

            if (! $product) {
                continue;
            }

            $qty = (int) $row['qty'];
            $price = (float) $product->price;
            $subtotal += $price * $qty;

            $lines[] = [
                'product' => $product,
                'qty' => $qty,
                'price' => $price,
                'option' => $row['option'] ?? null,
            ];
        }

        if ($lines === []) {
            return back()->withErrors(['items' => 'No valid items in the cart.']);
        }

        $coupon = $this->resolveCoupon($data['coupon'] ?? null, $subtotal);
        $discount = $this->discountFor($coupon, $subtotal);
        $total = max(0, $subtotal - $discount);

        $order = DB::transaction(function () use ($data, $lines, $subtotal, $discount, $total, $coupon, $request, $method): Order {
            $order = Order::create([
                'user_id' => $request->user()?->id,
                'email' => $data['email'],
                'ip' => $request->ip(),
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'delivery_status' => DeliveryStatus::Pending,
                'currency' => 'USD',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'coupon_id' => $coupon?->id,
                'discount_code' => $coupon?->code,
                'source' => 'storefront',
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'quantity' => $line['qty'],
                    'price' => $line['price'],
                    'status' => DeliveryStatus::Pending,
                    'form_data' => $line['option'] ? ['option' => $line['option']] : null,
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
     * Payment status webhook (IceNox notification_url). CSRF-exempt.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('IceNox webhook received', $payload);

        $paymentId = $payload['paymentid'] ?? $payload['payment_id'] ?? $payload['id'] ?? null;
        $statusRaw = strtolower((string) ($payload['status'] ?? $payload['state'] ?? $payload['payment_status'] ?? ''));
        $isPaid = ($payload['success'] ?? null) === true
            || in_array($statusRaw, ['paid', 'success', 'successful', 'completed', 'complete', '1', 'true'], true);

        $payment = $paymentId ? Payment::query()->where('transaction_id', $paymentId)->first() : null;
        $order = $payment?->order;

        if (! $order && ! empty($payload['orderid'])) {
            $order = Order::query()->where('order_number', $payload['orderid'])->first();
        }

        $isFailed = in_array($statusRaw, ['failed', 'fail', 'cancelled', 'canceled', 'declined', 'error', 'expired'], true);

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
        $methodLabel = \Illuminate\Support\Str::of($method)->replaceFirst('stripe-', '')->headline()->toString();

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
                'items' => $order->items->map(fn ($item): array => [
                    'id' => (string) $item->product_id,
                    'name' => $item->product_name,
                    'option' => $item->form_data['option'] ?? null,
                    'details' => array_filter(
                        is_array($item->form_data) ? $item->form_data : [],
                        fn ($v, $k): bool => $k !== 'option' && filled($v) && ! is_array($v),
                        ARRAY_FILTER_USE_BOTH,
                    ),
                    'qty' => $item->quantity,
                    'price' => (float) $item->price,
                ])->all(),
            ],
            'authed' => $request->user() !== null,
        ]);
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

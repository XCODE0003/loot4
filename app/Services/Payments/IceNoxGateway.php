<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IceNox Pay gateway — creates a hosted payment and returns the redirect URL.
 *
 * @see https://imp.icenox.com/api/payment/create/
 */
class IceNoxGateway
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly ?string $merchantId,
        private readonly string $baseUrl,
    ) {}

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    /**
     * Create a payment and return the IceNox checkout details.
     *
     * @return array{url: string, paymentid: ?string, reference: ?string}
     *
     * @throws PaymentException
     */
    public function createPayment(Order $order, string $method): array
    {
        // Webhook URL is overridable via config (e.g. a Cloudflare-bypassing
        // subdomain like hooks.loot4you.gg); falls back to the app route.
        $notificationUrl = config('services.icenox.notification_url') ?: route('checkout.webhook');

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->post(rtrim($this->baseUrl, '/').'/api/payment/create/', [
                'paymentmethod' => $method,
                'orderid' => $order->order_number,
                // Keep our own order number as-is; without this IceNox prefixes it
                // (e.g. "301-20000-<id>"), which would break webhook matching by orderid.
                'add_orderid_prefix' => false,
                'amount' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'total' => (float) $order->total,
                'currency' => $order->currency,
                'product' => 'Loot4you order '.$order->order_number,
                // IceNox creates a Stripe Customer (name + email attached to the
                // transaction) only when a non-empty customer_id is sent. Send the
                // full billing name and a stable merchant id — the account id for
                // logged-in buyers, the email for guests — so every order shows up
                // in Stripe. Field names must match IceNox exactly (customer_id,
                // not customerid, which IceNox ignores).
                'customer_name' => trim($order->first_name.' '.$order->last_name),
                'customer_email' => $order->email,
                'customer_id' => (string) ($order->user_id ?? $order->email),
                // Custom integration: status webhook is POSTed to notification_url.
                'notification_mode' => 'default',
                'notification_url' => $notificationUrl,
                'redirect_url' => route('checkout.success', $order->order_number),
            ]);

        $data = $response->json() ?? [];

        if (! $response->successful() || ! ($data['success'] ?? false) || blank($data['url'] ?? null)) {
            Log::warning('IceNox payment creation failed', [
                'order' => $order->order_number,
                'status' => $response->status(),
                'body' => $data,
            ]);

            throw new PaymentException($data['message'] ?? 'Could not start the payment. Please try again.');
        }

        Log::info('IceNox payment created', [
            'order' => $order->order_number,
            'paymentid' => $data['paymentid'] ?? null,
            'returned_orderid' => $data['orderid'] ?? null,
            'notification_url' => $notificationUrl,
        ]);

        return [
            'url' => $data['url'],
            'paymentid' => $data['paymentid'] ?? null,
            'reference' => $data['orderid'] ?? null,
        ];
    }
}

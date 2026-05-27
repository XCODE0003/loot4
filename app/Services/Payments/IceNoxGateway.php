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
        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->post(rtrim($this->baseUrl, '/').'/api/payment/create/', [
                'paymentmethod' => $method,
                'orderid' => $order->order_number,
                'amount' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'total' => (float) $order->total,
                'currency' => $order->currency,
                'product' => 'Loot4you order '.$order->order_number,
                'customer_email' => $order->email,
                'customerid' => (string) ($order->user_id ?? ''),
                'notification_url' => route('checkout.webhook'),
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

        return [
            'url' => $data['url'],
            'paymentid' => $data['paymentid'] ?? null,
            'reference' => $data['orderid'] ?? null,
        ];
    }
}

<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * Reminder for an order left unpaid — a "you're one step away, complete your
 * payment" email with a signed link that re-opens the hosted payment page.
 */
class AbandonedCartMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order)
    {
        $this->order->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address((string) config('mail.from.address'), 'Loot4You'),
            subject: 'Your Loot4You order '.$this->order->order_number.' is waiting',
        );
    }

    public function content(): Content
    {
        // Signed, 7-day link — anyone with it can only *pay* the order, nothing else.
        $payUrl = URL::temporarySignedRoute(
            'checkout.pay',
            now()->addDays(7),
            ['order' => $this->order->order_number],
        );

        return new Content(view: 'emails.order', with: [
            'order' => $this->order,
            'heading' => 'Your order is waiting',
            'intro' => 'You are one step away — this order has not been paid yet. '
                .'Complete your payment now and we will deliver your items right away.',
            'forCustomer' => true,
            'showContact' => false,
            'ctaUrl' => $payUrl,
            'ctaLabel' => 'Complete payment',
        ]);
    }
}

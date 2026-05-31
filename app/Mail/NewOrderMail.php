<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order)
    {
        $this->order->loadMissing(['items', 'payments']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address((string) config('mail.from.address'), 'Loot4You'),
            subject: '🎮 New paid order '.$this->order->order_number.' — '.number_format((float) $this->order->total, 2).' '.$this->order->currency,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order', with: [
            'order' => $this->order,
            'heading' => 'New order received',
            'intro' => 'A new paid order has just come in. Details are below.',
            'forCustomer' => false,
        ]);
    }
}

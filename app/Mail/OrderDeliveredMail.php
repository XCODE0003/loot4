<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order)
    {
        $this->order->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Loot4you order '.$this->order->order_number.' has been delivered');
    }

    public function content(): Content
    {
        return new Content(markdown: null, view: 'emails.order', with: [
            'order' => $this->order,
            'heading' => 'Your order has been delivered!',
            'intro' => 'Good news — your order has been delivered. Thank you for shopping with Loot4you! If anything is missing or you need help, just reply to this email or reach us at support@loot4you.gg with your order number.',
            'forCustomer' => true,
        ]);
    }
}

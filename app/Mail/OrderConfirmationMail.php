<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Order $order)
    {
        $this->order->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Loot4You order '.$this->order->order_number.' is confirmed');
    }

    public function content(): Content
    {
        return new Content(markdown: null, view: 'emails.order', with: [
            'order' => $this->order,
            'heading' => 'Thank you for your order!',
            'intro' => 'Your payment was received and your order is now being processed. A member of our team will deliver it shortly — you can track it any time from your account.',
            'forCustomer' => true,
        ]);
    }
}

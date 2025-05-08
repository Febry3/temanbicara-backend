<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $name, $bank, $expired_date, $amount, $va_number, $payment_method;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $bank, $expired_date, $amount, $va_number, $payment_method)
    {
        $this->name = $name;
        $this->bank = $bank;
        $this->expired_date = $expired_date;
        $this->amount = $amount;
        $this->va_number = $va_number;
        $this->payment_method = $payment_method;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'payment.payment-email',
            with: [
                'name' => $this->name,
                'bank' => $this->bank,
                'expired_date' => $this->expired_date,
                'amount' => $this->amount,
                'va_number' => $this->va_number,
                'payment_method' => $this->payment_method,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

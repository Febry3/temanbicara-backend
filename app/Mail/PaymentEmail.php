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

    private $name;
    private $bank;
    private $expiredDate;
    private $amount;
    private $vaNumber;
    private $paymentMethod;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $bank, $expiredDate, $amount, $vaNumber, $paymentMethod)
    {
        $this->name = $name;
        $this->bank = $bank;
        $this->expiredDate = $expiredDate;
        $this->amount = $amount;
        $this->vaNumber = $vaNumber;
        $this->paymentMethod = $paymentMethod;
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
                'expired_date' => $this->expiredDate,
                'amount' => $this->amount,
                'va_number' => $this->vaNumber,
                'payment_method' => $this->paymentMethod,
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

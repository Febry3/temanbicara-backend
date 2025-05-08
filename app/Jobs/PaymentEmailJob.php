<?php

namespace App\Jobs;

use App\Mail\PasswordResetEmail;
use App\Mail\PaymentEmail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PaymentEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;
    private $name, $bank, $expired_date, $amount, $va_number, $payment_method, $email;

    /**
     * Create a new job instance.
     */
    public function __construct($name, $bank, $expired_date, $amount, $va_number, $payment_method, $email)
    {
        $this->name = $name;
        $this->bank = $bank;
        $this->expired_date = $expired_date;
        $this->amount = $amount;
        $this->va_number = $va_number;
        $this->payment_method = $payment_method;
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new PaymentEmail($this->name, $this->bank, $this->expired_date, $this->amount, $this->va_number, $this->payment_method, $this->email));
    }
}

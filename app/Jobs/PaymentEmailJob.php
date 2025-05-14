<?php

namespace App\Jobs;

use App\Mail\PasswordResetEmail;
use App\Mail\PaymentEmail;
use Carbon\Carbon;
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
    private $name;
    private $bank;
    private $expired_date;
    private $amount;
    private $vaNumber;
    private $paymentMethod;
    private $email;

    /**
     * Create a new job instance.
     */
    public function __construct($name, $bank, $expired_date, $amount, $vaNumber, $paymentMethod, $email)
    {
        $this->name = $name;
        $this->bank = $bank;
        $this->expired_date = Carbon::parse($expired_date)->format('l, j F Y \a\t H:i');
        $this->amount = $amount;
        $this->vaNumber = $vaNumber;
        $this->paymentMethod = $paymentMethod;
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new PaymentEmail($this->name, $this->bank, $this->expired_date, $this->amount, $this->vaNumber, $this->paymentMethod, $this->email));
    }
}

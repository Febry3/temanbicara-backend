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
    private $expiredDate;
    private $amount;
    private $vaNumber;
    private $paymentMethod;
    private $email;

    /**
     * Create a new job instance.
     */
    public function __construct($name, $bank, $expiredDate, $amount, $vaNumber, $paymentMethod, $email)
    {
        $this->name = $name;
        $this->bank = $bank;
        $this->expiredDate = Carbon::parse($expiredDate)->format('l, j F Y \a\t H:i');
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
        Mail::to($this->email)->send(new PaymentEmail($this->name, $this->bank, $this->expiredDate, $this->amount, $this->vaNumber, $this->paymentMethod, $this->email));
    }
}

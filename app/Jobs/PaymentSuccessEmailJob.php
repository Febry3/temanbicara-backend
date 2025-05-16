<?php

namespace App\Jobs;

use App\Mail\PaymentSuccessEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class PaymentSuccessEmailJob implements ShouldQueue
{
    use Queueable;
    private $customerName;
    private $orderId;
    private $amount;
    private $email;
    /**
     * Create a new job instance.
     */
    public function __construct($customerName, $orderId, $amount, $email)
    {
        $this->customerName = $customerName;
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->email = $email;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new PaymentSuccessEmail($this->customerName, $this->orderId, $this->amount));
    }
}

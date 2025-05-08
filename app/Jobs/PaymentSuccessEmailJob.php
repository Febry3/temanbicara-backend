<?php

namespace App\Jobs;

use App\Mail\PaymentSuccessEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class PaymentSuccessEmailJob implements ShouldQueue
{
    use Queueable;
    private $customer_name, $order_id, $amount, $email;
    /**
     * Create a new job instance.
     */
    public function __construct($customer_name, $order_id, $amount, $email)
    {
        $this->customer_name = $customer_name;
        $this->order_id = $order_id;
        $this->amount = $amount;
        $this->email = $email;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new PaymentSuccessEmail($this->customer_name, $this->order_id, $this->amount));
    }
}

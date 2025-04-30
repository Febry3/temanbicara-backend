<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireConsultationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $paymentId;
    /**
     * Create a new job instance.
     */
    public function __construct($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Command consultation:expire dipanggil');
        $expiredPayments = \App\Models\Payment::where('payment_status', 'Pending')
            ->where('expired_date', '<', now())
            ->get();

        Log::info('Expired payments found: ' . $expiredPayments->count());

        if ($expiredPayments->isEmpty()) {
            Log::info('No expired payments found at this moment.');
        }

        foreach ($expiredPayments as $payment) {
            $payment->update(['payment_status' => 'Failed']);
            if ($payment->consultation) {
                $payment->consultation->update(['status' => 'Cancelled']);
                if ($payment->consultation->schedule) {
                    $payment->consultation->schedule->update(['status' => 'Available']);
                }
            }
        }
    }
}

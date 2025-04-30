<?php

namespace App\Jobs;

use App\Models\Consultations;
use App\Models\Payment;
use App\Models\Schedule;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class UpdateExpiredPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;
    /**
     * Create a new job instance.
     */

    private $payment;

    public function __construct(String $paymentId)
    {
        $this->payment =  Payment::find($paymentId);
    }

    /**
     * Execute the job.
     * Mengupdate status payment dan consultation setelah 15 menit data payment dibuat
     */
    public function handle(): void
    {
        Log::info('Works');
        $this->payment->update(['payment_status' => 'Expired']);

        $consultation = Consultations::where('payment_id', $this->payment->payment_id)->first();

        if (!$consultation) return;
        $consultation->update(['status' => 'Cancelled']);

        $schedule = Schedule::find($consultation->schedule_id);
        if (!$schedule) return;
        $schedule->update(['status' => 'Available']);
    }
}

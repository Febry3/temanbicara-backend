<?php

namespace App\Jobs;

use App\Models\Consultations;
use App\Models\Payment;
use App\Models\Schedule;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;

class UpdateExpiredPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $payment;
    /**
     * Create a new job instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment->withoutRelations();
    }

    /**
     * Execute the job.
     * Mengupdate status payment dan consultation setelah 15 menit data payment dibuat
     */
    public function handle(): void
    {
        $this->payment->update(['payment_status' => 'Expired']);
        $consultation = Consultations::where('payment_id', $this->payment->payment_id)->get();
        $consultation->update(['status' => 'Cancelled']);
        $schedule = Schedule::find($consultation->schedule_id);
        $schedule->update(['status' => 'Available']);
    }
}

<?php

namespace App\Jobs;

use App\Mail\PasswordResetEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordEmailJob implements ShouldQueue
{
    use Queueable;
    private $email;
    private $otp;
    /**
     * Create a new job instance.
     */
    public function __construct($email, $otp)
    {
        $this->email = $email;
        $this->otp = $otp;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new PasswordResetEmail(explode('@', $this->email)[0], $this->otp));
    }
}

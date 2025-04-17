<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ExpiredTimeConsultation::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        \Log::info('Task consultation:expire has been scheduled!');
        $schedule->command('consultation:expire')->everyMinute();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}




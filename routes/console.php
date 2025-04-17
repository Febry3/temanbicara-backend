<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\ExpiredTimeConsultation;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('consultation:expire', function () {
    (new ExpiredTimeConsultation)->handle();
})->purpose('Display an inspiring quote')->everyMinute();


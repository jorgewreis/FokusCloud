<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('fokus:prune-expired-data')->daily();
Schedule::command('fokus:apply-subscription-changes')->hourly();

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

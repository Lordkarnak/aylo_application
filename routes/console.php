<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Get new data and cache images again
// Command will run twice per day because we are not sure when exactly the json data changes
Schedule::command('app:get-pornstars')->twiceDaily();

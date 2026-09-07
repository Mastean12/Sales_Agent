<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Prospecting follow-up reminders, periodic ICP re-scoring, and other
// recurring revenue-operations jobs (execution plan section 4, "Scheduling")
// are added here once Prospect Intelligence / Outreach are built.
Schedule::command('model:prune')->daily();

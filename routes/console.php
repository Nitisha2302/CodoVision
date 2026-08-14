<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Queue IMAP sync (do not run IMAP inside web/Livewire requests — that causes timeout screens).
Schedule::job(new \Codovision\Crm\Jobs\SyncInboxJob(40))->everyMinute()->withoutOverlapping();

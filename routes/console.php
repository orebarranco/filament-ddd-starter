<?php

declare(strict_types=1);

/** Backups **/

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schedule;

if (Config::get('backup.schedule_enabled')) {
    Schedule::command('backup:clean')->dailyAt('00:30')->withoutOverlapping();
    Schedule::command('backup:run')->dailyAt('01:00')->withoutOverlapping();
    Schedule::command('backup:monitor')->dailyAt('01:30')->withoutOverlapping();
    Schedule::command('backup:run')->dailyAt('14:30')->withoutOverlapping();
    Schedule::command('backup:monitor')->dailyAt('15:00')->withoutOverlapping();
}

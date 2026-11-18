<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('vet:send-reminders')->dailyAt('08:00');

Schedule::command('backup:clean')->dailyAt('01:30');
Schedule::command('backup:run')->dailyAt('02:00');

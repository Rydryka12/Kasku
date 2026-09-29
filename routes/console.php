<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('recurring:run')->dailyAt('00:05');

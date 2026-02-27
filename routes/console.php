<?php

use Illuminate\Support\Facades\Schedule;

// Poll OpenClaw for activity every minute
Schedule::command('lunaos:poll-openclaw')->everyMinute();

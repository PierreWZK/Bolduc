<?php

use Illuminate\Support\Facades\Schedule;

// Rappel J-7 chaque matin ; la commande ne renvoie jamais deux fois le même rappel.
Schedule::command('bolduc:rappels')->dailyAt('09:00');

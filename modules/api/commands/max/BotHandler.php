<?php

namespace app\modules\api\commands\max;

use garmayev\max\EventHandler;

interface BotHandler
{
    public function register(EventHandler $handler): void;
}
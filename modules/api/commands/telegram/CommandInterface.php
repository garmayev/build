<?php

namespace app\modules\api\commands\telegram;

interface CommandInterface
{
    public function handle($telegram, $args);
}
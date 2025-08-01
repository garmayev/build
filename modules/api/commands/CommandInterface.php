<?php

namespace app\modules\api\commands;

interface CommandInterface
{
    public function handle($telegram, $args);
}
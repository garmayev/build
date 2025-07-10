<?php

namespace app\modules\notifications\handlers;

class InlineHandlerButton implements Handler
{

    public static function run(array $params): bool
    {
        return true;
    }
}
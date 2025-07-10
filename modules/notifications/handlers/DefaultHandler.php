<?php

namespace app\modules\notifications\handlers;

class DefaultHandler implements Handler
{

    public static function run(array $params): bool
    {
        return true;
    }
}
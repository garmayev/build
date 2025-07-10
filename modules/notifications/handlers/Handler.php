<?php

namespace app\modules\notifications\handlers;

interface Handler
{
    public static function run(array $params): bool;
}
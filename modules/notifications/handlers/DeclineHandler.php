<?php

namespace app\modules\notifications\handlers;

class DeclineHandler implements Handler
{

    public static function run(array $args): bool
    {
        \Yii::error("Decline handler executed");
        \Yii::error($args);
        return true;
    }
}
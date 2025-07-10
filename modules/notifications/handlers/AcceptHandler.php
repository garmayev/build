<?php

namespace app\modules\notifications\handlers;

class AcceptHandler implements Handler
{

    public static function run(array $args): bool
    {
        \Yii::error('Accept handler executed');
        \Yii::error($args);
        return true;
    }
}
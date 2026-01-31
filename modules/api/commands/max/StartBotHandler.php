<?php

namespace app\modules\api\commands\max;

use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;

class StartBotHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $max->sendMessage([
            "text" => "Hello World",
        ], [
            "user_id" => $handler->getUser()->id,
        ]);
    }
}
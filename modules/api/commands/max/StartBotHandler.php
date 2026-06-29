<?php

namespace app\modules\api\commands\max;

use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\MessageBuilder;

class StartBotHandler implements BotHandler
{
    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onBotStarted(function ($request) use ($max) {
            $message = MessageBuilder::create(\Yii::t("telegram", "command_start"))
                ->format('html')
                ->inlineKeyboard([
                    [MessageBuilder::requestContactButton(\Yii::t("telegram", "command_contact"), "id032396478430_2_bot")]
                ])
                ->build();
            $max->sendMessage($message, [
                "user_id" => $request->user->user_id,
            ]);
            
        });
    }
}
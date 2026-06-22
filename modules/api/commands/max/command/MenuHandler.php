<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class MenuHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            if ($request->callback->payload === "command_menu") {
                $max->sendAnswer(
                    ['message' => MessageBuilder::create(\Yii::t('telegram', 'message_menu'),)
                        ->inlineKeyboard([
                            MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t("telegram", "command_orders_my"), "command_orders_my")]),
                            MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t("telegram", "command_orders_list"), "command_orders_list")]),
                            MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t("telegram", "command_stats"), "command_stats")]),
                        ])
                        ->format("html")
                        ->build()],
                    ["callback_id" => $request->callback->callback_id]
                );
            }
        });
    }
}
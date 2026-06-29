<?php

namespace app\modules\api\commands\max\message;

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
        $handler->regex('/\/menu/', function ($request) use ($max) {
            $session = \Yii::$app->session;
            if ($session->getIsActive()) {
                $session->close();
            }

            // 3. Set the target session ID 
            $session->setId($request->message->sender->user_id);
            // 4. Open the session with the newly assigned ID
            $session->open();
            $session->removeAll();
            $max->sendMessage(
                MessageBuilder::create(\Yii::t('telegram', 'message_menu'))
                    ->inlineKeyboard([
                        MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t("telegram", "command_orders_my"), "command_orders_my")]),
                        MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t("telegram", "command_orders_list"), "command_orders_list")]),
                        MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t("telegram", "command_start_day"), "command_start_day")]),
                        MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t("telegram", "command_start_report"), "command_start_report")])
                    ])
                    ->format("html")
                    ->build(), 
                ['user_id' => $request->message->sender->user_id]
            );
        });
    }
}
<?php

namespace app\modules\api\commands\max\command;

use app\components\Helper;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\MessageBuilder;

class RejectHandler implements BotHandler
{
    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            $args = explode(' ', $request->callback->payload);
            parse_str($args[1] ?? '', $data);

            $orderId = $data["id"] ?? null;

            $order = \app\models\Order::findOne($orderId);
            $coworker = \app\models\User::find()
                ->joinWith('profile')
                ->where(['profile.max_id' => $request->callback->user->user_id])
                ->one();
//            \Yii::error($data);
            if ($args[0] === "command_reject") {
                $order->unlink('coworkers', $coworker, true);
                $max->sendAnswer([
                    'message' => MessageBuilder::create(\Yii::t('telegram', 'You reject order successfully'))
                        ->inlineKeyboard([
                            MessageBuilder::row([
                                MessageBuilder::callbackButton(\Yii::t('telegram', 'command_back'), 'command_menu')
                            ])
                        ])
                        ->format('html')
                        ->build(),
                ], [
                    'callback_id' => $request->callback->callback_id,
                ]);
            }
        });
    }
}
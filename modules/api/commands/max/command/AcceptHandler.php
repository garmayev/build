<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class AcceptHandler implements BotHandler
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
            if ($args[0] === "command_accept") {
                if (!$order->isFull()) {
                    if (!$order->canAssignCoworker($coworker)) {
                        $max->sendAnswer(['message' => [
                            'text' => \Yii::t('app', 'Sorry, you can`t assign to order'),
                            'attachments' => [],
                        ]], [
                            'callback_id' => $request->callback->callback_id,
                        ]);
                        return null;
                    }
                    if (!$order->assignCoworker($coworker)) {
                        $max->sendAnswer(['message' => [
                            'text' => \Yii::t('app', 'Sorry, you can`t assign to order'),
                            'attachments' => [],
                            'format' => 'html',
                        ]], [
                            'callback_id' => $request->callback->callback_id,
                        ]);
                        return null;
                    }
                    if ($order->isFull()) {
                        $order->status = \app\models\Order::STATUS_PROCESS;
                        $order->save();
                    }
                }
                $messages = \app\models\telegram\TelegramMessage::find()->where(['order_id' => $order->id])->andWhere(['not in', 'chat_id', $request->callback->user->user_id])->all();
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $max->editMessage([
                            'text' => $message->text,
                            'format' => 'html',
                            'attachments' => $order->isFull() ? json_decode($message->attachments, true) : [],
                        ], [
                            'message_id' => $message->message_id,
                        ]);
                    }
                }
            }
        });
    }
}
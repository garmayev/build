<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class AcceptCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        parse_str($args[0] ?? '', $data);
        $orderId = $data["order_id"] ?? null;

        $order = \app\models\Order::findOne($orderId);
        $coworker = \app\models\User::find()
            ->joinWith('profile')
            ->where(['profile.chat_id' => $query->from["id"]])
            ->one();

        if (!$order->isFull()) {
            if (!$order->assignCoworker($coworker)) {
                return null;
            }

            $messages = \app\models\telegram\TelegramMessage::find()->where(['order_id' => $order->id])->all();
            if (count($messages)) {
                if ($order->isFull()) {
                    $order->status = \app\models\Order::STATUS_PROCESS;
                    $order->save();
                    foreach ($messages as $message) {
                        if (in_array($message->chat_id, array_merge(\yii\helpers\ArrayHelper::map($order->coworkers, 'profile.chat_id', 'profile.chat_id'), [$order->owner->profile->chat_id => $order->owner->profile->chat_id]))) {
                            $message->editMessageText(\app\components\Helper::generateTelegramHiddenMessage($order->id), null);
                        } else {
                            $message->remove();
                        }
                    }
                    if ($order->owner->profile->chat_id) {
                        $message->editMessageText(\app\components\Helper::generateTelegramHiddenMessage($order->id), null);
                    }
                } else {
                    foreach ($messages as $message) {
                        $header = $message->chat_id == $coworker->profile->chat_id ?
                            \Yii::t('app', 'You have agreed to complete the order') . " #{$order->id}\n" :
                            \Yii::t('app', 'New Order') . " #{$order->id}\n";

                        // Для сотрудника, который согласился, убираем кнопки
                        if ($message->chat_id == $query->from['id']) {
                            $replyMarkup = json_encode(['inline_keyboard' => []]);
                        } else {
                            $replyMarkup = $message->reply_markup;
                        }
                        $text = "";
                        $message->editMessageText(
                            $header . \app\components\Helper::generateTelegramMessage($order->id),
                            $replyMarkup
                        );
                    }
                }
            } else {
                $telegram->editMessageText([
                    'message_id' => $query->message['message_id'],
                    'text' => \app\components\Helper::orderDetails($order),
                    'reply_markup' => null,
                ]);
            }
        }
    }
}
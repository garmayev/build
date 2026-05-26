<?php

namespace app\modules\api\commands\telegram\callback;

use app\modules\api\commands\telegram\CommandInterface;

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
            if (!$order->canAssignCoworker($coworker)) {
//                $message = \app\models\telegram\TelegramMessage::findOne(['message_id' => $query->id]);
                $telegram->sendMessage([
                    'chat_id' => $coworker->profile->chat_id,
                    'text' => \Yii::t('app', 'Sorry, you can`t assign to order'),
                ]);
                return null;
            }
            if (!$order->assignCoworker($coworker)) {
                return null;
            }

            $messages = \app\models\telegram\TelegramMessage::find()->where(['order_id' => $order->id])->all();

//            \Yii::error(count($messages));
            if (count($messages)) {
                if ($order->isFull()) {
                    $order->status = \app\models\Order::STATUS_PROCESS;
                    $order->save();
                    foreach ($messages as $message) {
                        if (in_array($message->chat_id, array_merge(\yii\helpers\ArrayHelper::map($order->coworkers, 'profile.chat_id', 'profile.chat_id'), [$order->owner->profile->chat_id => $order->owner->profile->chat_id]))) {
                            $message->editMessage(\app\components\Helper::generateTelegramHiddenMessage($order->id));
                        } else {
                            $message->remove();
                        }
                    }
                } else {
                    foreach ($messages as $message) {
                        $title = !empty($order->title) ? " ({$order->title})" : "";
                        $header = $message->chat_id == $coworker->profile->chat_id ?
                            "<b>".\Yii::t('app', 'You have agreed to complete the order') . " #{$order->id} {$title}</b>\n" :
                            "<b>" . \Yii::t('app', 'New Order') . " #{$order->id} {$title}</b>\n";

                        // Для сотрудника, который согласился, убираем кнопки
                        if ($message->chat_id == $query->from['id']) {
                            $replyMarkup = json_encode(["inline_keyboard" => []]);
                        } else {
                            $replyMarkup = $message->reply_markup;
                        }
                        $text = "";
//                        \Yii::error($header);
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
                    'chat_id' => $query->from["id"],
                    'parse_mode' => 'html',
                    'reply_markup' => null,
                ]);
            }
        } else {
            $order->status = \app\models\Order::STATUS_PROCESS;
            $messages = \app\models\telegram\TelegramMessage::find()->where(['order_id' => $order->id])->all();
            $order->save();
            \Yii::error(count($messages));
            foreach ($messages as $message) {
                if (in_array($message->chat_id, array_merge(\yii\helpers\ArrayHelper::map($order->coworkers, 'profile.chat_id', 'profile.chat_id'), [$order->owner->profile->chat_id => $order->owner->profile->chat_id]))) {
                    $message->editMessageText(\app\components\Helper::generateTelegramHiddenMessage($order->id), null);
                } else {
                    $message->remove();
                }
            }
        }
    }
}
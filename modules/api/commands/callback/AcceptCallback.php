<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class AcceptCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        parse_str($args[0] ?? '', $data);
        $orderId = $data["order_id"] ?? null;

        if (!$orderId) {
            $telegram->answerCallbackQuery([
                'chat_id' => $this->query->from['id'],
                'callback_query_id' => $this->query->id,
                'text' => \Yii::t('telegram', 'query_accept')
            ]);
            return ['ok' => false];
        }

        $order = \app\models\Order::findOne($orderId);
        $coworker = \app\models\User::find()->joinWith('profile')->where(['profile.chat_id' => $telegram->input->callback_query->from["id"]]);
        $coworker = $coworker->one();

        if (!$order || !$coworker) {
            \Yii::error([
                "ok" => false,
                "message" => "Missing {$coworker->name} or order #{$order->id}"
            ]);
        }

        // Add coworker to order
        // \Yii::error($order->isFull());
        if (!$order->isFull()) {
            if (!$order->assignCoworker($coworker)) {
                \Yii::error(["ok" => false, "message" => "Coworker {$coworker->name} is already agreed to order #{$order->id}"]);
                return null;
            }

            $messages = \app\models\telegram\TelegramMessage::find()->where(['order_id' => $order->id])->all();
            if (count($messages)) {
                // If order is now complete, update status
                if ($order->isFull()) {
                    $order->status = \app\models\Order::STATUS_PROCESS;
                    $order->save();
                    if (YII_ENV === 'prod') {
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
                        return $messages;
                    }
                } else {
                    foreach ($messages as $message) {
                        $header = $message->chat_id == $coworker->profile->chat_id ?
                            \Yii::t('app', 'You have agreed to complete the order') . " #{$order->id}\n" :
                            \Yii::t('app', 'New Order') . " #{$order->id}\n";

                        // Для сотрудника, который согласился, убираем кнопки
                        if ($message->chat_id == $telegram->input->callback_query->from['id']) {
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
                    'message_id' => $telegram->input->callback_query->message['message_id'],
                    'text' => \app\components\Helper::orderDetails($order),
                    'reply_markup' => null,
                ]);
            }
        }
    }
}
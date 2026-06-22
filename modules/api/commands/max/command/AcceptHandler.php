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
                        \Yii::error("Can`t assign to order {$order->id}");
                        $max->sendAnswer(['message' => [
                            'text' => \Yii::t('app', 'Sorry, you can`t assign to order'),
                            'attachments' => [],
                            'format' => 'html',
                        ]], [
                            'callback_id' => $request->callback->callback_id,
                        ]);
                        return null;
                    } else {
                        \Yii::error("Order {$order->id} successfully assigned with coworker {$coworker->profile->family} {$coworker->profile->name}");
                    }

                    $messages = \app\models\telegram\TelegramMessage::find()->where(['order_id' => $order->id])->all();
                    if (count($messages)) {
                        if ($order->isFull()) {
                            $order->status = \app\models\Order::STATUS_PROCESS;
                            $order->save();
                            foreach ($messages as $message) {
                                if (in_array($message->chat_id, array_merge(\yii\helpers\ArrayHelper::map($order->coworkers, 'profile.chat_id', 'profile.chat_id'), [$order->owner->profile->chat_id => $order->owner->profile->chat_id]))) {
//                                    \Yii::error($request->attributes);
                                    \Yii::$app->max->editMessage(
                                        MessageBuilder::create(\app\components\Helper::generateTelegramMessage($order->id))
                                            ->inlineKeyboard([])
                                            ->format('html')
                                            ->build(),
                                        ['message_id' => $message->message_id]
                                    );
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

                                $max->sendAnswer(['message' => [
                                    'text' => $header . \app\components\Helper::generateTelegramMessage($order->id),
                                    'format' => 'html',
                                    'attachments' => [],
                                ]], ['callback_id' => $request->callback->callback_id]);
                            }
                        }
                    } else {
                        \Yii::$app->max->sendAnswer(['message' => [
                            'text' => \app\components\Helper::orderDetails($order),
                            'format' => 'html',
                            'attachments' => [],
                        ]], ['callback_id' => $request->callback->callback_id]);
                    }
                } else {
                    $order->status = \app\models\Order::STATUS_PROCESS;
                    $messages = \app\models\telegram\TelegramMessage::find()->where(['order_id' => $order->id])->all();
                    $order->save();
                    \Yii::error(count($messages));
                    foreach ($messages as $message) {
                        if (in_array($message->chat_id, array_merge(\yii\helpers\ArrayHelper::map($order->coworkers, 'profile.chat_id', 'profile.chat_id'), [$order->owner->profile->chat_id => $order->owner->profile->chat_id]))) {
                            \Yii::$app->max->sendAnswer(['message' => [
                                'text' => \app\components\Helper::generateTelegramHiddenMessage($order->id),
                                'format' => 'html',
                                'attachments' => [],
                            ]], ['callback_id' => $request->callback->callback_id]);
                        } else {
                            $message->remove();
                        }
                    }
                }
            }
        });
    }
}
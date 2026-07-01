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
                        $max->sendAnswer([
                            'message' => MessageBuilder::create(\Yii::t('telegram', 'command_accept_failed'))
                                ->inlineKeyboard([
                                    MessageBuilder::row([
                                        MessageBuilder::callbackButton(\Yii::t('telegram', 'button_menu'), 'command_menu')
                                    ])
                                ])
                                ->build(),
                            ], [
                                'callback_id' => $request->callback->callback_id,
                            ]);
                        return null;
                    }
                    if (!$order->assignCoworker($coworker)) {
                        $max->sendAnswer([
                            'message' => MessageBuilder::
                                create(\Yii::t('telegram', 'command_accept_failed'))
                                ->inlineKeyboard([
                                    MessageBuilder::row([
                                        MessageBuilder::callbackButton(\Yii::t('telegram', 'button_menu'), 'command_menu')
                                    ])
                                ])
                                ->build(),
                        ], [
                            'callback_id' => $request->callback->callback_id,
                        ]);
                        return null;
                    }
                    if (isset($order) && $order->isFull()) {
                        $order->status = \app\models\Order::STATUS_PROCESS;
                        $order->save();
                    }
                }
                $messages = \app\models\telegram\TelegramMessage::find()->where(['order_id' => $order->id])->andWhere(['not in', 'chat_id', array_merge([$request->callback->user->user_id], \yii\helpers\ArrayHelper::getColumn($order->coworkers, 'profile.max_id'))])->all();
                if (count($messages)) {
                    foreach ($messages as $message) {
                        $messageText = \app\components\Helper::orderDetails($order);
                        $title = !empty($this->title) ? "({$this->title})" : "";
                        $formattedMessage = '<b>' . \Yii::t('app', 'Order #{id}', ['id' => $orderId]) . " {$title}</b>\n" . $messageText;

                        $builded = MessageBuilder::create($formattedMessage)->format('html')->build();
                        $builded['attachments'] = $order->isFull() ? [] : json_decode($message->reply_markup, true);
                        $max->editMessage($builded, [
                            'message_id' => $message->message_id,
                        ]);
                    }
                }
                $max->sendAnswer([
                    'message' => MessageBuilder::create(\Yii::t('telegram', 'command_accept_successfully'))
                        ->inlineKeyboard([MessageBuilder::row([
                            MessageBuilder::callbackButton(\Yii::t('telegram', 'button_menu'), 'command_menu')
                        ])])
                        ->build(),
                    ], [
                        'callback_id' => $request->callback->callback_id,
                    ]);
            }
        });
    }
}
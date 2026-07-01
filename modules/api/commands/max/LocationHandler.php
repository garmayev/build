<?php

namespace app\modules\api\commands\max;

use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\MessageBuilder;

class LocationHandler implements BotHandler
{
    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onMessage(function ($request) use ($max) {
            $session = \Yii::$app->session;
            if ($session->getIsActive()) {
                $session->close();
            }
            $user = \app\models\User::findByMaxId($request->message->sender->user_id);
            \Yii::$app->user->login($user, 0);
            $session->setId($user->profile->max_id);
            $session->open();
            $attachments = $request->message->body->attachments;
            foreach ($attachments as $attachment) {
                if ($attachment->type == 'location') {
                    $order = \app\models\Order::findOne($session->get('order_id'));
                    \Yii::error([$attachment->latitude, $attachment->longitude]);
                    \Yii::error($order->building->location->attributes);
                    if (\app\components\Helper::isPointInCircle(['latitude' => $attachment->latitude, 'longitude' => $attachment->longitude], $order->building->location->attributes, $order->building->radius)) {
                        $hours = new \app\models\Hours([
                            'user_id' => $user->id,
                            'date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d'),
                            'count' => 0,
                            'is_payed' => 0,
                            'order_id' => $order->id,
                            'start_time' => \Yii::$app->formatter->asDatetime(time(), 'php:Y-m-d H:i:s'),
                        ]);
                        if ($hours->save()) {
                            $max->sendMessage(
                                MessageBuilder::create(\Yii::t('telegram', 'command_hours_created'))
                                    ->inlineKeyboard([
                                        MessageBuilder::row([
                                            MessageBuilder::callbackButton(\Yii::t('telegram', 'button_menu'), 'command_menu')
                                        ])
                                    ])
                                    ->build(), [
                                'user_id' => $request->message->sender->user_id
                            ]);
                        } else {
                            \Yii::error($hours->errors);
                        }
                    } else {
                        $message = MessageBuilder::create(\Yii::t('telegram', 'command_location_missing'))
                            ->inlineKeyboard([MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'button_menu'), 'command_menu')])])
                            ->build();
                        $max->sendMessage($message, ['user_id' => $user->profile->max_id]);
                    }
                }
            }
        });
    }
}
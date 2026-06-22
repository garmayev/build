<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class OrderDetailHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            $args = explode(' ', $request->callback->payload);
            if ($args[0] === "order_detail") {
                parse_str($args[1] ?? '', $data);
                $user = \app\models\Coworker::findByMaxId($request->callback->user->user_id);
                $order = \app\models\Order::findOne($data['id']);
                $text = \app\components\Helper::orderDetails($order, 'max');
                $keyboard = [];
                if ($user) {
                    \Yii::$app->user->login($user);
                }
                if ($user->can("director")) {
                    foreach (\app\models\Order::findAll(["created_by" => $user->id]) as $order) {
                        $keyboard[] = [[ 'text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order_detail mode=my&id=' . $order->id ]];
                    }
                } else {
                    if ($data['mode'] === 'my') {
                        $keyboard[] = [MessageBuilder::callbackButton(\Yii::t('telegram', 'button_reject'), "command_reject id={$order->id}")];
                        $keyboard[] = [MessageBuilder::callbackButton(\Yii::t('telegram', 'command_back'), 'command_orders_my')];
                    } else {
                        $keyboard[] = [MessageBuilder::callbackButton(\Yii::t('telegram', 'button_accept'), "command_accept id={$order->id}")];
                        $keyboard[] = [MessageBuilder::callbackButton(\Yii::t('telegram', 'command_back'), 'command_orders_list')];
                    }
                    $message = MessageBuilder::create($text)->inlineKeyboard($keyboard)->format('html');
                    foreach ($order->attachments as $attachment) {
                        if ($attachment->isImage()) {
                            $message->image(\yii\helpers\Url::to($attachment->url, true));
//                        } else {
//                            $message->file(\yii\helpers\Url::to($order->attachments[0]->url, true));
                        }
                    }
                    $max->sendAnswer(['message' => $message->build()], ['callback_id' => $request->callback->callback_id]);
                }
            }
        });
    }
}
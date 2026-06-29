<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class OrdersMyHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            if ($request->callback->payload === "command_orders_my") {
                $user = \app\models\Coworker::findByMaxId($request->callback->user->user_id);
                $text = "";
                $keyboard = [];
                if ($user) {
                    \Yii::$app->user->login($user);
                }

                if ($user->can("director")) {
                    $text = \Yii::t("telegram", 'command_orders_my');
                    foreach (\app\models\Order::findAll(["created_by" => $user->id]) as $order) {
                        $keyboard[] = [[ 'text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order_detail mode=my&id=' . $order->id ]];
                    }
                } else {
                    $orders = $user->orders;
//                    \Yii::error(count($orders));
                    if ($orders) {
                        foreach ($orders as $order) {
                            $keyboard[] = [MessageBuilder::callbackButton(!empty($order->comment) ? $order->comment :  \Yii::t('app', 'Order #{id}', ['id' => $order->id]), "order_detail mode=my&id={$order->id}")];
                        }
                    }
                    $keyboard[] = [MessageBuilder::callbackButton(\Yii::t('telegram', 'command_back'), 'command_menu')];
                    $text = \Yii::t("telegram", 'command_order_list');
                    $max->sendAnswer(['message' => MessageBuilder::create($text)->inlineKeyboard($keyboard)->build()], ['callback_id' => $request->callback->callback_id]);
                }
            }
        });
    }
}
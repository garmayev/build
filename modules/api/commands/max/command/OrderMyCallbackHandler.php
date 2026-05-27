<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;

class OrderMyCallbackHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function ($request) use ($max) {
//            \Yii::error($request);
             $user = \app\models\User::findByMaxId($request->user->user_id);
             $text = "";
             $keyboard = [];
             if ($user->can("director")) {
                 \Yii::$app->user->login($user);
                 $text = \Yii::t("telegram", 'command_orders_my');
                 foreach (\app\models\Order::findAll(["created_by" => $user->id]) as $order) {
                     $keyboard[] = [[ 'text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order_detail mode=my&id=' . $order->id ]];
                 }
             } else {
                 foreach ($user->orders as $order) {
                     $keyboard[] = [[ 'text' => \Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'callback_data' => '/order_detail mode=my&id=' . $order->id ]];
                 }
                 $text = \Yii::t("telegram", 'command_order_list');
                 \Yii::error($user);
             }
        });
    }
}
<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class OrdersListHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            if ($request->callback->payload === "command_orders_list") {
                $user = \app\models\Coworker::findByMaxId($request->callback->user->user_id);
                $text = "";
                $keyboard = [];
                if ($user) {
                    \Yii::$app->user->login($user);
                    $coworker = Coworker::find()->joinWith(['profile'])->where(['user_id' => $user->id])->one();
                }

                $orders = $coworker->suitableOrders;
                if ($orders) {
                    foreach ($orders as $order) {
                        $model = \app\models\Order::findOne($order);
                        if ($model) {
                            $keyboard[] = MessageBuilder::row([MessageBuilder::callbackButton(!empty($model->comment) ? $model->comment : \Yii::t('app', 'Order #{id}', ['id' => $model->id]), "order_detail mode=list&id={$model->id}")]);
                        }
                    }
                }
                $keyboard[] = [MessageBuilder::callbackButton(\Yii::t('telegram', 'command_back'), 'command_menu')];
                $text = \Yii::t("telegram", 'command_order_list');
                $max->sendAnswer(['message' => MessageBuilder::create($text)->inlineKeyboard($keyboard)->build()], ['callback_id' => $request->callback->callback_id]);
            }
        });
    }
}
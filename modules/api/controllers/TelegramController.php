<?php

namespace app\modules\api\controllers;

use aki\telegram\base\Command;
use app\models\Coworker;
use app\models\telegram\TelegramMessage;
use app\models\User;
use app\models\Order;
use app\models\Telegram;
use yii\helpers\ArrayHelper;

class TelegramController extends \yii\web\Controller
{
    private $query;
    private $params;

    public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionCallback()
    {
        $telegram = \Yii::$app->telegram;
        \aki\telegram\base\Command::run("/start", function ($telegram, $args) {
            \Yii::error($args);
        });
        \aki\telegram\base\Command::run("/agree", function ($telegram, $args) {
            \Yii::error($args);
        });
        \aki\telegram\base\Command::run("/dis-agree", function ($telegram, $args) {
            \Yii::error($args);
        });
        Command::run("/projects", function ($telegram, $args) {
            \Yii::error($args);
        });
        return [];
    }

    public function call($action, $data)
    {
        switch ($action) {
            case '/start':
                $this->start($data);
                break;
        }
    }

    private function ok()
    {
        $order = Order::findOne($this->params['order_id']);
        $coworker = Coworker::findOne(['chat_id' => $this->query['callback_query']['from']['id']]);
        $currentMessage = TelegramMessage::findOne(['message_id' => $this->query['callback_query']['message']['message_id']]);
        $messages = TelegramMessage::find()
            ->where(['order_id' => $order->id]);
        if (!$order->checkSuccessfully()) {
            $order->link('coworkers', $coworker);
            if ($order->checkSuccessfully()) {
                $order->status = Order::STATUS_PROCESS;
                $order->save();
            }
            $currentMessage->status = TelegramMessage::STATUS_AGREE;
            $currentMessage->reply_markup = null;
            $currentMessage->save();
            foreach ($messages->all() as $message) {
                $header = $message->status ?
                    \Yii::t('app', 'You have agreed to complete the order') . " #{$order->id}" :
                    \Yii::t('app', 'New order') . " #{$order->id}";
                $message->editText(
                    null,
                    $order->generateTelegramText($header)
                );
            }
        } else {
            $order->status = Order::STATUS_PROCESS;
            $order->save();
            foreach ($messages->andWhere(['NOT IN', 'chat_id', ArrayHelper::map($order->coworkers, 'chat_id', 'chat_id')])->all() as $message) {
                $message->remove();
            }
        }
        return [];
    }

    private function cancel()
    {
        $message = TelegramMessage::find()->where(['id' => $this->query['callback_query']['message']['message_id']])->one();
        $message->remove();
    }

    private function start()
    {
        $coworker = \app\models\Coworker::findOne($this->params["data"]);
        if ($coworker) {
            \Yii::error($this->params);
//            $coworker->chat_id = "" . $this->query['message']['from']['id'];
//            if ($coworker->save()) {
//                $messages = [
//                    [
//                        'chat_id' => $coworker->chat_id,
//                        'text' => \Yii::t('app', 'Welcome to our bot'),
//                        'reply_markup' => null
//                    ], [
//                        'chat_id' => $coworker->chat_id,
//                        'text' => \Yii::t('app', 'Here you will get orders to your services'),
//                    ]
//                ];
//                foreach ($messages as $messageConfig) {
//                    $telegramMessage = new TelegramMessage($messageConfig);
//                    $telegramMessage->send();
//                }
//            } else {
//                \Yii::error($coworker->getErrorSummary(true));
//            }
        }
    }

    private function invite()
    {

    }

    private function new()
    {

    }
}

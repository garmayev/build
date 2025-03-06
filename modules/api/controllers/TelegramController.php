<?php

namespace app\modules\api\controllers;

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
        $this->query = json_decode(file_get_contents("php://input"), true);

//        \Yii::error($this->query);

        $message = isset($this->query['message']) ? $this->query['message'] : $this->query['callback_query'];
        if (isset($this->query['message']) && isset($this->query['message']['entities'])) {
            foreach ($this->query['message']['entities'] as $entity) {
                if ($entity['type'] === 'bot_command') {
//                    \Yii::error( substr($this->query['message']['text'], 0, $entity['length']) );
                }
            }
            $args = explode(" ", substr($this->query['message']['text'], 1, strlen($this->query['message']['text']) - 1));
        }
        if (isset($this->query['callback_query'])) {
            parse_str($this->query['callback_query']['data'], $this->params);
        } else {
            $this->params["action"] = array_shift($args);
            $this->params["data"] = $args;
        }

//        \Yii::error($this->params);
        return parent::beforeAction($action);
    }

    public function actionCallback()
    {
        if (isset($this->params)) {
            $this->{$this->params['action']}();
        }
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
            $coworker->chat_id = "" . $this->query['message']['from']['id'];
            if ($coworker->save()) {
                $messages = [
                    [
                        'chat_id' => $coworker->chat_id,
                        'text' => \Yii::t('app', 'Welcome to our bot'),
                        'reply_markup' => null
                    ], [
                        'chat_id' => $coworker->chat_id,
                        'text' => \Yii::t('app', 'Here you will get orders to your services'),
                    ]
                ];
                foreach ($messages as $messageConfig) {
                    $telegramMessage = new TelegramMessage($messageConfig);
                    $telegramMessage->send();
                }
            } else {
                \Yii::error($coworker->getErrorSummary(true));
            }
        }
    }

    private function invite()
    {

    }
}

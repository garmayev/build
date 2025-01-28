<?php

namespace app\modules\api\controllers;

use app\models\telegram\TelegramMessage;
use app\models\User;
use app\models\Order;
use app\models\Telegram;

class TelegramController extends \yii\web\Controller {
    private $query;
    private $params;

    public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->enableCsrfValidation = false;
        $this->query = json_decode( file_get_contents("php://input"), true );

        \Yii::error($this->query);

        $message = isset($this->query['message']) ? $this->query['message'] : $this->query['callback_query'];
        if ( isset($this->query['message']) && isset($this->query['message']['entities']) ) {
            foreach ($this->query['message']['entities'] as $entity) {
                if ($entity['type'] === 'bot_command') {
//                    \Yii::error( substr($this->query['message']['text'], 0, $entity['length']) );
                }
            }
        }
        $args = explode(" ", substr($this->query['message']['text'], 1, strlen($this->query['message']['text']) - 1));
        if ( isset($this->query['callback_query']) ) {
            parse_str( $this->query['callback_query']['data'], $this->params );
        } else {
            $this->params["action"] = array_shift($args);
            $this->params["data"] = $args;
        }

//        \Yii::error($this->params);
        return parent::beforeAction($action);
    }

    public function actionCallback()
    {
        if ( isset($this->params) ) {
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
        $user = User::findOne(['chat_id' => $this->query['callback_query']['from']['id']]);
        if (!$order->check()) {
            $order->addCoworker($user);
            $message = TelegramMessage::findOne(['id' => $this->query['callback_query']['message']['message_id']]);
            if ( $message->load(["TelegramMessage" => ["text" => $order->generateTelegramText(\Yii::t('app', 'You have agreed to complete the order')." #{$order->id}"), "status" => TelegramMessage::STATUS_AGREE, "reply_markup" => null]]) && $message->save() ) {
                $message->editText();
            }
            if ( $order->check() ) {
                foreach ( TelegramMessage::find()->where(['order_id' => $order->id])->andWhere(['status' => TelegramMessage::STATUS_NEW])->all() as $message ) {
                    $message->remove();
                }
                $order->status = Order::STATUS_PROCESS;
                $order->save();
            }
        } else {
            $messages = TelegramMessage::find()->where(['order_id' => $order->id])->andWhere(['status' => TelegramMessage::STATUS_NEW])->all();
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
        $user = User::findOne($coworker->user_id);
        $user->chat_id = "".$this->query['message']['from']['id'];
        if ( $user->save() ) {
            \Yii::error( $user->attributes );
        } else {
            \Yii::error( $user->getErrorSummary(true) );
        }
    }

    private function invite()
    {
        
    }
}

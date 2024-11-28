<?php

namespace app\modules\api\controllers;

use app\models\telegram\TelegramMessage;
use app\models\User;
use app\models\Order;
use app\models\Telegram;

class TelegramController extends \yii\web\Controller {
    private $query;
    private $data;
    public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->enableCsrfValidation = false;
        $this->query = json_decode( file_get_contents("php://input"), true );
        parse_str( $this->query['callback_query']['data'], $this->data );
        return parent::beforeAction($action);
    }

    public function actionCallback() {
        if ( isset($this->data) ) {
            $this->{$this->data['action']}();
            \Yii::error( $this->query );
        }
        return [];
    }

    private function ok()
    {
        \Yii::error( "ok" );
        $order = Order::findOne($this->data['order_id']);
        $user = User::findOne(['chat_id' => $this->query['callback_query']['from']['id']]);
        if ( !$order->check($user) ) {
            $order->addCoworker($user);
            $message = TelegramMessage::findOne(['id' => $this->query['callback_query']['message']['message_id']]);
            $message->load(["TelegramMessage" => ["text" => "You are agree for this order!", "reply_markup" => []]]);
            $message->edit();
        } else {
            $messages = TelegramMessage::findAll(['order_id' => $order->id]);
            foreach ($messages as $message) {
                $message->remove();
            }
        }
    }

    private function cancel() 
    {
        $message = TelegramMessage::findOne(['id' => $this->query['callback_query']['message']['message_id']]);
        $message->load(["TelegramMessage" => ["text" => "You are cancelled from this order!", "reply_markup" => []]]);
        $message->edit();
    }
}

?>
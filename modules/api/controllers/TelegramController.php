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
        if ( isset($this->query['callback_query']) ) {
            parse_str( $this->query['callback_query']['data'], $this->data );
        }
        return parent::beforeAction($action);
    }

    public function actionCallback() {
        if ( isset($this->data) ) {
            $this->{$this->data['action']}();
        }
        return [];
    }

    private function ok()
    {
        $order = Order::findOne($this->data['order_id']);
        $user = User::findOne(['chat_id' => $this->query['callback_query']['from']['id']]);
        if (!$order->check()) {
            $order->addCoworker($user);
            $message = TelegramMessage::findOne(['id' => $this->query['callback_query']['message']['message_id']]);
            if ( $message->load(["TelegramMessage" => ["text" => $order->generateTelegramText(\Yii::t('app', 'You have agreed to complete the order')." #{$order->id}"), "status" => TelegramMessage::STATUS_AGREE, "reply_markup" => null]]) && $message->save() ) {
                $message->editText();
//                $message->editKeyboard();
            } else {
                \Yii::error($message->getErrorSummary(true));
            }
            if ( $order->check() ) {
                foreach ( TelegramMessage::find()->where(['order_id' => $order->id])->andWhere(['status' => TelegramMessage::STATUS_NEW])->all() as $message ) {
                    $message->remove();
                }
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
}

?>

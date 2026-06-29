<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class ReportSaveHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onMessage(function (\garmayev\max\base\Request $request) use ($max) {
            $session = \Yii::$app->session;
            if ($session->getIsActive()) {
                $session->close();
            }

            // 3. Set the target session ID 
            $session->setId($request->message->sender->user_id);
            // 4. Open the session with the newly assigned ID
            $session->open();
            //\Yii::error($request->message->sender->user_id);
            $orderId = $session->get('order_id');
            $urls = $session->get('urls');
            $action = $session->get('action');
            if (isset($action)) {
                $model = new \app\models\Report();
                if ($model->load(['Report' => ['order_id' => $orderId, 'urls' => $urls, 'comment' => $request->message->body->text]]) && $model->save()) {
                    $message = MessageBuilder::create(\Yii::t('telegram', 'message_report_{id}_attached_to_{order_id}', ['id' => $model->id, 'order_id' => $orderId]), [])->build();
                    $max->sendMessage($message, ['user_id' => $request->message->sender->user_id]);
                } else {
                    \Yii::error($model->errors);
                }
            }
        });
    }
}
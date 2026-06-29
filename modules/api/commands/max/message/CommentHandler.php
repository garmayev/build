<?php

namespace app\modules\api\commands\max\message;

use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\MessageBuilder;

class CommentHandler implements BotHandler
{
    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onMessage(function ($request) use ($max) {
            $session = \Yii::$app->session;
            if ($session->getIsActive()) {
                $session->close();
            }

            $session->setId($request->message->sender->user_id);
            $session->open();

            $orderId = $session->get('order_id');
            $urls = $session->get('urls');
            $action = $session->get('action');
            $report_id = $session->get('report_id');

            \Yii::error($action);
            \Yii::error($report_id);

            if (trim($request->message->body->text) === "/menu") {
                $session->removeAll();
                return;
            }
            if ($action === 'report_comment' && isset($report_id)) {
                $model = \app\models\Report::findOne($report_id);
                if (isset($model)) {
                    if ($model->load(['Report' => ['order_id' => $orderId, 'comment' => $request->message->body->text]]) && $model->save()) {
                        $model->setUrl($urls);
                        \Yii::error($action);
                        \Yii::error($model->attributes);
                        $message = MessageBuilder::create(\Yii::t('telegram', 'message_report_{id}_attached_to_{order_id}', ['id' => $model->id, 'order_id' => $orderId]))->build();
    //                    $max->sendMessage($message, ['user_id' => $request->message->sender->user_id]);
                        $session->removeAll();
                    } else {
                        \Yii::error($model->attributes);
                        \Yii::error($model->errors);
                    }
                }
            }
        });
    }
}
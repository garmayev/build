<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class ReportCommentHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            $args = explode(' ', $request->callback->payload);
            parse_str($args[1] ?? '', $data);

            $session = \Yii::$app->session;
            if ($session->getIsActive()) {
                $session->close();
            }
            $session->setId($request->callback->user->user_id);
            $session->open();

            if ($args[0] === "command_report_comment") {
                $order_id = $session->get('order_id');
                $session->set('action', 'report_comment');
                $message = MessageBuilder::create(\Yii::t('telegram', 'send a comment for order #{id}', ['id' => $order_id]))->build();
                $message['attachments'] = [];
                $max->sendAnswer(["message" => $message], ['callback_id' => $request->callback->callback_id]);
//                $order_id = $session->get('order_id');
//                $report
            }
        });
    }
}
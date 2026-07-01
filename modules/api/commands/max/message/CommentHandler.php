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

            if (trim($request->message->body->text) === "/menu") {
                $session->removeAll();
                return;
            }
            $action = $session->get('action');
            $report_id = $session->get('report_id');
            $order_id = $session->get('order_id');

            if ($action === 'report_comment') {
                $comment = $session->get('comment');
                $comment .= "\n".$request->message->body->text;
                $model = \app\models\Report::findOne($report_id);
                $session->set('comment', $comment);
                $message = MessageBuilder::create(\Yii::t('telegram', 'message_text_or_image_and_save'))
                    ->inlineKeyboard([
                        MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'button_report_save'), 'command_report_save')]),
                        MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'button_menu'), 'command_menu')])
                    ])
                    ->build();
                $max->sendMessage($message, ['user_id' => $request->message->sender->user_id]);
            }
        });
    }
}
<?php

namespace app\modules\api\commands\max;

use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\MessageBuilder;

class ImageHandler implements BotHandler
{
    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onMessage(function ($request) use ($max) {
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
            $action = $session->get('action');
            switch ($action) {
                case "new_report":
                    $urls = [];
                    foreach ($request->message->body->attachments ?? [] as $attachment) {
                        if ($attachment->type == 'image' && isset($orderId) && isset($action)) {
                            $urls[] = $attachment->payload->url;
                        }
                    }
                    \Yii::error($action);
                    if (count($urls)) {
                        $session->set('urls', $urls);
                        $max->sendMessage(
                            MessageBuilder::create(\Yii::t('app', 'Images saved')."\n\n".\Yii::t('telegram', 'Are you want to attach text for report?'))
                                ->inlineKeyboard([
                                    MessageBuilder::row([
                                        MessageBuilder::callbackButton(\Yii::t('app', 'Yes'), 'command_report_comment')
                                    ]),
                                    MessageBuilder::row([
                                        MessageBuilder::callbackButton(\Yii::t('app', 'No'), 'command_report_save')
                                    ]),
                                ])
                                ->build(), 
                            ['user_id' => $request->message->sender->user_id]
                        );
                    }
                break;
                case "continue_report":
                break;
            }
        });
    }
}
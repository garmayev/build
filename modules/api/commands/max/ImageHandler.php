<?php

namespace app\modules\api\commands\max;

use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\MessageBuilder;

class ImageHandler implements BotHandler
{
    private $_user;
    private $_session;

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onMessage(function ($request) use ($max) {
            if ($this->auth($request->message->sender)) {
                $this->switchSession();
            }

            $action = $this->_session->get('action');
            $urls = $this->_session->get('urls');
            $comment = $this->_session->get('comment');
            $isset_images = false;
            $isset_text = false;
            $text = "";
            switch ($action) {
                case "new_report":
                    $orderId = $this->_session->get('order_id');
                    $urls = $this->_session->get('urls') ?? [];
                    foreach ($request->message->body->attachments ?? [] as $attachment) {
                        if (($attachment->type == 'image') && isset($orderId) && isset($action)) {
                            $urls[] = $attachment->payload->url;
                            $isset_images = true;
                        }
                        if ($attachment->type == 'file') {
                            $urls[] = $attachment->payload['url'];
                            $isset_images = true;
                        }
                    }

                    if ($isset_images) {
                        $this->_session->set('urls', $urls);
                        $text .= \Yii::t('telegram', 'message_images_attached_to_report') . "\n\n";
                    }
                    if ($request->message->body->text) {
                        $comment .= "\n".$request->message->body->text;
                        $this->_session->set('comment', $comment);
                        $text .= \Yii::t('telegram', 'message_comment_attached_to_report') . "\n\n";
                    }
                    $text .= \Yii::t('telegram', 'message_text_or_image_and_save');
                    $message = MessageBuilder::create($text)
                        ->inlineKeyboard([
                            MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'button_report_save'), 'command_report_save')]),
                            MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'button_report_cancel'), 'command_report_cancel')])
                        ])
                        ->build();
                    $max->sendMessage($message, ['user_id' => $request->message->sender->user_id]);
                break;
                case "continue_report":
                break;
            }
        });
    }

    private function auth(\garmayev\max\types\User $max_user)
    {
        $this->_user = \app\models\User::findByMaxId($max_user->user_id);
        if ($this->_user) {
            \Yii::$app->user->login($this->_user, 0);
            return true;
        } else {
            return false;
        }
    }

    private function switchSession()
    {
        if (!\Yii::$app->user->isGuest) {
            $this->_session = \Yii::$app->session;
            if ($this->_session->getIsActive()) {
                $this->_session->close();
            }

            // 3. Set the target session ID 
            $this->_session->setId(\Yii::$app->user->identity->profile->max_id);
            // 4. Open the session with the newly assigned ID
            $this->_session->open();
        }
    }
}
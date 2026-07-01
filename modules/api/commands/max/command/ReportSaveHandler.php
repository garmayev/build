<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class ReportSaveHandler implements BotHandler
{
    private $_session;
    private $_user;

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            $args = explode(' ', $request->callback->payload);

            if ($this->auth($request->callback->user)) {
                $this->switchSession();
            }

            $orderId = $this->_session->get('order_id');
            $urls = $this->_session->get('urls');
            $action = $this->_session->get('action');
            $comment = $this->_session->get('comment');
            if ($args[0] === "command_report_save") {
                $model = new \app\models\Report();
                if ($urls || $comment) {
                    if ($model->load(['Report' => ['order_id' => $orderId, 'comment' => $comment]]) && $model->save()) {
                        $model->setUrl($urls);
                        $message = MessageBuilder::create(\Yii::t('telegram', 'message_report_{id}_attached_to_{order_id}', ['id' => $model->id, 'order_id' => $orderId]))
                            ->inlineKeyboard([MessageBuilder::row([
                                MessageBuilder::callbackButton(\Yii::t('telegram', 'View report #{id}', ['id' => $model->id]), 'command_report_view')
                            ]), MessageBuilder::row([
                                MessageBuilder::callbackButton(\Yii::t('telegram', 'button_menu'), 'command_menu')
                            ])])
                            ->build();
                        $max->sendAnswer(['message' => $message], ['callback_id' => $request->callback->callback_id]);
                        $this->_session->removeAll();
                    }
                } else {
                    $max->sendAnswer(['message' => MessageBuilder::create(\Yii::t('telegram', 'Nothing to attach!!!'))->build()], ['callback_id' => $request->callback->callback_id]);
                }
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
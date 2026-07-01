<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class OrderDetailHandler implements BotHandler
{
    private $_session;
    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            $args = explode(' ', $request->callback->payload);
            if ($args[0] === "order_detail") {
                if (!$this->auth($request->callback->user)) {
                    return false;
                }
                $this->switchSession();
                parse_str($args[1] ?? '', $data);
                $user = \app\models\Coworker::findByMaxId($request->callback->user->user_id);
                $order = \app\models\Order::findOne($data['id']);
                $text = \app\components\Helper::orderDetails($order, 'max');
                $keyboard = [];
                if ($data['mode'] === 'my') {
                    $this->_session->set('action', 'new_report');
                    $keyboard[] = [MessageBuilder::callbackButton(\Yii::t('telegram', 'button_reject'), "command_reject id={$order->id}")];
                    $keyboard[] = [MessageBuilder::callbackButton(\Yii::t('telegram', 'button_report_start'), "command_start_report_order id={$order->id}")];
                    $keyboard[] = [MessageBuilder::callbackButton(\Yii::t('telegram', 'button_back'), 'command_orders_my')];
                } else {
                    $keyboard[] = [MessageBuilder::callbackButton(\Yii::t('telegram', 'button_accept'), "command_accept id={$order->id}")];
                    $keyboard[] = [MessageBuilder::callbackButton(\Yii::t('telegram', 'button_back'), 'command_orders_list')];
                }
                $message = MessageBuilder::create($text)->inlineKeyboard($keyboard)->format('html');
                foreach ($order->attachments as $attachment) {
                    if ($attachment->isImage()) {
                        $message->image(\yii\helpers\Url::to($attachment->url, true));
//                        } else {
//                            $message->file(\yii\helpers\Url::to($order->attachments[0]->url, true));
                    }
                }
                $max->sendAnswer(['message' => $message->build()], ['callback_id' => $request->callback->callback_id]);
            }
        });
    }

    private function auth(\garmayev\max\types\User $max_user)
    {
        $coworker = \app\models\Coworker::find()->joinWith('profile')->where(['profile.max_id' => $max_user->user_id]);
        $user = \app\models\User::findByMaxId($max_user->user_id);
        if ($user) {
            \Yii::$app->user->login($user, 0);
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
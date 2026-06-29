<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class StartReportOrderHandler implements BotHandler
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

            // 3. Set the target session ID 
            $session->setId($request->callback->user->user_id);
            // 4. Open the session with the newly assigned ID
            $session->open();

            $coworker = Coworker::find()->joinWith('profile')->where(['profile.max_id' => $request->callback->user->user_id])->one();

            if ($args[0] === "command_start_report_order" && $session->get('action') != null) {
                $session->set('order_id', $data['id']);
                $message = MessageBuilder::create(\Yii::t('app', 'Send a photo for report order #{id}', ['id' => $data['id']]))
                    ->build();
                $message['attachments'] = [];
                $max->sendAnswer([
                    'message' => $message,
                ], [
                    'callback_id' => $request->callback->callback_id,
                ]);
            }
        });
    }
}
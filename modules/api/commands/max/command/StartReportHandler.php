<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class StartReportHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            $session = \Yii::$app->session;
            if ($session->getIsActive()) {
                $session->close();
            }

            // 3. Set the target session ID 
            $session->setId($request->callback->user->user_id);
            // 4. Open the session with the newly assigned ID
            $session->open();

            $coworker = Coworker::find()->joinWith('profile')->where(['profile.max_id' => $request->callback->user->user_id])->one();

            if ($request->callback->payload === "command_start_report") {
                $keyboard = [];
                $session->set('action', 'new_report');
                foreach ($coworker->orders as $order) {
                    $keyboard[] = MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('app', 'Order #{id}', ['id' => $order->id]), "command_start_report_order id={$order->id}")]);
                }
                $keyboard[] = MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'command_back'), 'command_menu')]);
                $max->sendAnswer([
                    'message' => MessageBuilder::create(\Yii::t('app', 'Create new Report')."\n\n".\Yii::t('telegram', 'Select an order for report'))
                        ->inlineKeyboard($keyboard)
                        ->build(),
                ], [
                    'callback_id' => $request->callback->callback_id,
                ]);
            }
        });
    }
}
<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class StartDayHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            $session = \Yii::$app->session;
            if ($session->getIsActive()) {
                $session->close();
            }
            $user = \app\models\User::findByMaxId($request->callback->user->user_id);
            \Yii::$app->user->login($user, 0);
            // 3. Set the target session ID 
            $session->setId('your-specific-session-id-here');
            // 4. Open the session with the newly assigned ID
            $session->open();

            if ($request->callback->payload === "command_start_day") {
                $keyboard = [];
                $coworker = Coworker::findOne($user->id);
                foreach ($coworker->orders as $order) {
                    $keyboard[] = MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('app', 'Order #{id}', ['id' => $order->id]), 'command_start_day_order id='.$order->id)]);
                }
                $keyboard[] = MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'command_back'), 'command_menu')]);
                $max->sendAnswer([
                    'message' => MessageBuilder::create(\Yii::t('app', 'Select order from list:'))
                        ->inlineKeyboard($keyboard)
                        ->build()
                ], [
                    'callback_id' => $request->callback->callback_id
                ]);
            }
        });
    }
}
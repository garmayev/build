<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class StartDayOrderHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            $args = explode(' ', $request->callback->payload);
//            \Yii::error($args);
            $session = \Yii::$app->session;
            if ($session->getIsActive()) {
                $session->close();
            }
            $user = \app\models\User::findByMaxId($request->callback->user->user_id);
            \Yii::$app->user->login($user, 0);
            // 3. Set the target session ID 
            $session->setId($user->profile->max_id);
            // 4. Open the session with the newly assigned ID
            $session->open();

            if ($args[0] === "command_start_day_order") {
                parse_str($args[1] ?? '', $data);
                $session->set('order_id', $data['id']);

                $keyboard[] = MessageBuilder::row([MessageBuilder::requestLocationButton(\Yii::t('telegram', 'command_location_send'), true)]);
                $keyboard[] = MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'command_back'), 'command_start_day')]);
                $max->sendAnswer([
                    'message' => MessageBuilder::create(\Yii::t('telegram', 'command_location_send'))
                        ->inlineKeyboard($keyboard)
                        ->build()
                ], [
                    'callback_id' => $request->callback->callback_id
                ]);
            }
        });
    }
}
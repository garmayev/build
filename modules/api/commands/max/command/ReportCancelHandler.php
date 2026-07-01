<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class ReportCancelHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            $args = explode(' ', $request->callback->payload);
            $session = \Yii::$app->session;
            if ($session->getIsActive()) {
                $session->close();
            }

            // 3. Set the target session ID 
            $session->setId($request->callback->user->user_id);
            // 4. Open the session with the newly assigned ID
            $session->open();
            //\Yii::error($request->message->sender->user_id);
            if ($args[0] === "command_report_cancel") {
                $message = MessageBuilder::create(\Yii::t('telegram', 'message_report_cancelled'))
                    ->inlineKeyboard([MessageBuilder::row([
                        MessageBuilder::callbackButton(\Yii::t('telegram', 'button_menu'), 'command_menu')
                    ])])
                    ->build();
                $max->sendAnswer(['message' => $message], ['callback_id' => $request->callback->callback_id]);
                $session->removeAll();
            }
        });
    }
}
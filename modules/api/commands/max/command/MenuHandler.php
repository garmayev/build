<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class MenuHandler implements BotHandler
{

    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            if ($request->callback->payload === "command_menu") {
                if ($this->auth($request->callback->user)) {
                    $max->sendAnswer(
                        ['message' => MessageBuilder::create(\Yii::t('telegram', 'message_menu'),)
                            ->inlineKeyboard($this->getKeyboard())
                            ->format("html")
                            ->build()],
                        ["callback_id" => $request->callback->callback_id]
                    );
                }
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

    private function getKeyboard()
    {
        $keyboard = [];
        $keyboard[] = MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'command_orders_my'), 'command_orders_my')]);
        $keyboard[] = MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'command_orders_list'), 'command_orders_list')]);
        if ($this->isDayStarted()) {
            if (!$this->isDayStopped()) {
                $keyboard[] = MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'command_stop_day'), 'command_stop_day')]);
            }
        } else {
            $keyboard[] = MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'command_start_day'), 'command_start_day')]);
        }
        $keyboard[] = MessageBuilder::row([MessageBuilder::callbackButton(\Yii::t('telegram', 'button_report_start'), 'command_start_report')]);

        return $keyboard;
    }

    private function isDayStarted()
    {
        $user_id = \Yii::$app->user->getId();
        $hours = \app\models\Hours::find()->where(['user_id' => $user_id])->andWhere(['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')])->one();
        return !empty($hours->start_time);
    }

    private function isDayStopped()
    {
        $user_id = \Yii::$app->user->getId();
        $hours = \app\models\Hours::find()->where(['user_id' => $user_id])->andWhere(['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')])->one();
        return !empty($hours->stop_time);
    }
}
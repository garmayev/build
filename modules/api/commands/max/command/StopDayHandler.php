<?php

namespace app\modules\api\commands\max\command;

use app\models\Coworker;
use app\modules\api\commands\max\BotHandler;
use garmayev\max\EventHandler;
use garmayev\max\types\Callback;
use garmayev\max\MessageBuilder;

class StopDayHandler implements BotHandler
{
    private $_user;
    public function register(EventHandler $handler): void
    {
        $max = \Yii::$app->max;
        $handler->onCallback(function (\garmayev\max\base\Request $request) use ($max) {
            if ($this->auth($request->callback->user)) {
                if ($request->callback->payload === "command_stop_day") {
                    $hours = \app\models\Hours::find()->where(['user_id' => $this->_user->id])->andWhere(['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')])->one();
                    if ($hours) {
                        $hours->stop_time = \Yii::$app->formatter->asDatetime(time(), 'php:Y-m-d H:i:s');
                        $hours->count = $this->diffTime($hours->stop_time, $hours->start_time);
                        if ($hours->save()) {
                            $max->sendAnswer(['message' => MessageBuilder::create(\Yii::t('telegram', 'command_hours_closed'))
                                ->inlineKeyboard([MessageBuilder::row([
                                    MessageBuilder::callbackButton(\Yii::t('telegram', 'button_menu'), 'command_menu')
                                ])])
                                ->build()
                            ], [
                                'callback_id' => $request->callback->callback_id
                            ]);
                            return;
                        } else {
                            \Yii::error($hours->errors);
                        }
                    }
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

    private function diffTime($time1, $time2)
    {
        $date1 = new \DateTime($time1);
        $date2 = new \DateTime($time2);

        // Вычисляем разницу
        $interval = $date1->diff($date2);

        // Считаем общее количество часов
        $hours = ($interval->days * 24) + $interval->h; 
        // Если нужны и минуты:
        // $minutes = ($hours * 60) + $interval->i;
        return abs($hours) + 1;
    }
}
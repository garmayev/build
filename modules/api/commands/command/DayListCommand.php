<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;

class DayListCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $user = \app\models\User::findByChatId($message->from->id);
        $debit = 0;
        $credit = 0;
        $workedHours = 0;
        $year = date("Y");
        $month = date("m");
        $startDate = date("$year-$month-01");
        $finishDate = date("$year-$month-".cal_days_in_month(CAL_GREGORIAN, $month, $year));
        $monthNum = intval($month);
        $keyboard = [
            [
                ['text' => \Yii::t('telegram', 'button_previous'), 'callback_data' => '/day_list month=' . (intval($monthNum) - 1).'&year='.$year],
//                ['text' => \Yii::t('telegram', 'next').' >', 'callback_data' => '/day_list month=' . (intval($month) + 1).'&year='.$year],
            ]
        ];
        $hours = \app\models\Hours::find()
            ->where(['user_id' => $user->id])
            ->andWhere(['>=', 'date', "$year-$month-01"])
            ->andWhere(['<=', 'date', "$year-$month-".cal_days_in_month(CAL_GREGORIAN, $month, $year)])
            ->groupBy(['date'])
            ->all();
        $text = "<b>".\Yii::t('app', 'Started').'</b>: <i>'.\Yii::$app->formatter->asDate("$year-$month-01")."</i>\n".
            "<b>".\Yii::t('app', 'Stopped').'</b>: <i>'.\Yii::$app->formatter->asDate("$year-$month-".cal_days_in_month(CAL_GREGORIAN, $month, $year))."</i>\n";
//        \Yii::error(count($hours));
        foreach ($hours as $hour) {
            if ($hour->is_payed) {
                $debit += $hour->count * $hour->getPrice();
            } else {
                $credit += $hour->count * $hour->getPrice();
            }
            $workedHours += $hour->count;
            $keyboard[] = [
                ['text' => \Yii::$app->formatter->asDate($hour->date), 'callback_data' => '/day_view date='.$hour->date],
            ];
        }
        $text .= "<b>".\Yii::t('app', 'is_paid')."</b>: <i>".\Yii::$app->formatter->asCurrency($debit)."</i>\n";
        $text .= "<b>".\Yii::t('app', 'not_paid')."</b>: <i>".\Yii::$app->formatter->asCurrency($credit)."</i>\n";
        $text .= "<b>".\Yii::t('app', 'Total sum')."</b>: <i>".\Yii::$app->formatter->asCurrency($debit + $credit)."</i>\n";
        $text .= "<b>".\Yii::t('app', 'Total hours')."</b>: <i>".$workedHours."</i>\n";
        $keyboard[] = [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/menu']];
        $telegram->sendMessage([
            'chat_id' => $message->from->id,
            'text' => (count($keyboard) === 1) ? \Yii::t('telegram', 'command_empty') : \Yii::t('telegram', 'command_order_list'),
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard,
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
            ])
        ]);
    }
}
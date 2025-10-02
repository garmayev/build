<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class DayListCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        $user = \app\models\User::findByChatId($query->from['id']);
        $keyboard = [];
        $debit = $credit = $workedHours = $paidHours = $unPaidHours = $ordersCount = 0;
        if (count($args)) {
            parse_str($args[0] ?? '', $data);
            $month = intval($data['month']);
            $year = intval($data['year']);
            if ($month === 0) {
                $month = 12;
                $year = $year - 1;
            } else if ($month === 13) {
                $month = 1;
                $year = $year + 1;
            }
            if ($month === intval(date('m'))) {
                $keyboard = [
                    [
                        ['text' => \Yii::t('telegram', 'button_previous'), 'callback_data' => '/day_list month='.($month - 1).'&year='.$year],
                    ]
                ];
            } else {
                $keyboard = [
                    [
                        ['text' => \Yii::t('telegram', 'button_previous'), 'callback_data' => '/day_list month='.($month - 1).'&year='.$year],
                        ['text' => \Yii::t('telegram', 'button_next'), 'callback_data' => '/day_list month='.($month + 1).'&year='.$year],
                    ]
                ];
            }
        } else {
            $month = intval(date('m'));
            $year = intval(date('Y'));
            $keyboard = [
                [['text' => \Yii::t('telegram', 'button_previous'), 'callback_data' => '/day_list month='.($month - 1).'&year='.$year]],
            ];
        }
        $hours = \app\models\Hours::find()
            ->where(['user_id' => $user->id])
            ->andWhere(['>=', 'date', "$year-$month-01"])
            ->andWhere(['<=', 'date', "$year-$month-".cal_days_in_month(CAL_GREGORIAN, $month, $year)])
            ->groupBy(['date'])
            ->all();
        $dateMonth = \DateTime::createFromFormat('m', $month);
        $text = "<b>".\Yii::t('app', 'Stats per {eq}', ['eq' => \Yii::t('app', $dateMonth->format('F'))])." {$year}</b>\n\n";
//        \Yii::error(count($hours));
        foreach ($hours as $hour) {
            $keyboard[] = [
                ['text' => \Yii::$app->formatter->asDate($hour->date), 'callback_data' => '/day_view date='.$hour->date],
            ];
        }
        foreach ($user->getHoursByMonth("$year-$month-01", "$year-$month-".cal_days_in_month(CAL_GREGORIAN, $month, $year)) as $hour) {
            if ($hour->is_payed) {
                $debit += $hour->count * $hour->getPrice();
                $paidHours += $hour->count;
            } else {
                $credit += $hour->count * $hour->getPrice();
                $unPaidHours += $hour->count;
            }
            $workedHours += $hour->count;
            $ordersCount++;
        }
        $text .= "<b>".\Yii::t('app', 'Orders count')."</b>: <i>".$ordersCount."</i>\n\n";
        
        $text .= "<b>".\Yii::t('app', 'is_paid')."</b>: <i>".\Yii::$app->formatter->asCurrency($debit)."</i>\n";
        $text .= "<b>".\Yii::t('app', 'not_paid')."</b>: <i>".\Yii::$app->formatter->asCurrency($credit)."</i>\n";
        $text .= "<b>".\Yii::t('app', 'Total sum')."</b>: <i>".\Yii::$app->formatter->asCurrency($debit + $credit)."</i>\n\n";

        $text .= "<b>".\Yii::t('app', 'is_paid_hours')."</b>: <i>".$paidHours."</i>\n";
        $text .= "<b>".\Yii::t('app', 'not_paid_hours')."</b>: <i>".$unPaidHours."</i>\n";
        $text .= "<b>".\Yii::t('app', 'Total hours')."</b>: <i>".$workedHours."</i>\n";
        $keyboard[] = [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/menu']];
        $telegram->editMessageText([
            'chat_id' => $query->from['id'],
            'message_id' => $query->message['message_id'],
            'text' => $text,
            'parse_mode' => 'html',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ]);
    }
}
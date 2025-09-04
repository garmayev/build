<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class DayViewCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        $user = \app\models\User::findByChatId($query->from['id']);
        $keyboard = [];
        parse_str($args[0] ?? '', $data);
        $date = $data['date'];
        $debit = $credit = $workedHours = $paidHours = $unPaidHours = 0;
        $text = "";

        $hours = \app\models\Hours::find()->where(['user_id' => $user->id])->andWhere(['date' => $date])->all();
        $text = "<b>".\Yii::t('app', 'Stats per {eq}', ['eq' => \Yii::$app->formatter->asDate($date)])."</b>\n\n";
            
        foreach ($hours as $hour) {
            if ($hour->is_payed) {
                $debit += $hour->count * $hour->getPrice();
                $paidHours += $hour->count;
            } else {
                $credit += $hour->count * $hour->getPrice();
                $unPaidHours += $hour->count;
            }
            $workedHours += $hour->count;
            $keyboard[] = [['text' => \Yii::t('app', 'Order #{id}', ['id' => $hour->order_id]), 'callback_data' => '/day_detail date='.$hour->date.'&order_id='.$hour->order_id]];
        }
        $date_array = explode('-', $date);
        $text .= "<b>".\Yii::t('app', 'Orders count')."</b>: <i>".count($hours)."</i>\n\n";
        $text .= "<b>".\Yii::t('app', 'is_paid')."</b>: <i>".\Yii::$app->formatter->asCurrency($debit)."</i>\n";
        $text .= "<b>".\Yii::t('app', 'not_paid')."</b>: <i>".\Yii::$app->formatter->asCurrency($credit)."</i>\n";
        $text .= "<b>".\Yii::t('app', 'Total sum')."</b>: <i>".\Yii::$app->formatter->asCurrency($debit + $credit)."</i>\n\n";
        $text .= "<b>".\Yii::t('app', 'is_paid_hours')."</b>: <i>".$paidHours."</i>\n";
        $text .= "<b>".\Yii::t('app', 'not_paid_hours')."</b>: <i>".$unPaidHours."</i>\n";
        $text .= "<b>".\Yii::t('app', 'Total hours')."</b>: <i>".$workedHours."</i>\n";
        $keyboard[] = [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => "/day_list year={$date_array[0]}&month={$date_array[1]}"]];
        $keyboard[] = [['text' => \Yii::t('telegram', 'button_menu'), 'callback_data' => '/menu']];
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
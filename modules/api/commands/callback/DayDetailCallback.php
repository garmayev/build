<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\callback\BaseCallback;
use app\modules\api\commands\CommandInterface;

class DayDetailCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $query = $telegram->input->callback_query;
        $user = \app\models\User::findByChatId($query->from['id']);
        $keyboard = [];
        parse_str($args[0] ?? '', $data);
        $date = $data['date'];
        $order = \app\models\Order::findOne($data['order_id']);
        $hour = \app\models\Hours::find()
            ->where(['user_id' => $user->id])
            ->andWhere(['order_id' => $data['order_id']])
            ->andWhere(['date' => $data['date']])
            ->one();
        $text = "<b>".\Yii::t('app', 'Date')."</b>: <i>".\Yii::$app->formatter->asDate($hour->date)."</i>\n".
            "<b>".\Yii::t('app', 'Order')."</b>: <i>#{$hour->order_id}</i>\n".
            "<b>".\Yii::t('app', 'Count')."</b>: <i>{$hour->count}</i>\n".
            "<b>".\Yii::t('app', 'Price')."</b>: <i>".$hour->getPrice()."</i>\n".
            "<b>".\Yii::t('app', 'Is payed')."</b>: ".($hour->is_payed ? "\u{2705}" : "\u{274C}")."\n";
        \Yii::$app->formatter->timeZone = 'UTC';
        if ($hour->start_time) {
            $text .= "<b>".\Yii::t('app', 'Started')."</b>: <i>".\Yii::$app->formatter->asDatetime($hour->start_time)."</i>\n";
        }
        if ($hour->stop_time) {
            $text .= "<b>".\Yii::t('app', 'Stopped')."</b>: <i>".\Yii::$app->formatter->asDatetime($hour->stop_time)."</i>\n";
        }
        $text .= "\n<b>".\Yii::t('app', 'Total sum')."</b>: <i>".($hour->getPrice() * $hour->count)."</i>";
        $keyboard[] = [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/day_view date='.$hour->date]];
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
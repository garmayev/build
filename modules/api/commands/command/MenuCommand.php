<?php

namespace app\modules\api\commands\command;

use app\modules\api\commands\CommandInterface;

class MenuCommand extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $message = $telegram->input->message;
        $user = \app\models\User::findByChatId($message->from->id);
        $hour = \app\models\Hours::find()->where(['and', ['user_id' => $user->id], ['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')], ['stop_time' => null]])->one();
        $isHoursIsset = isset( $hour );
        $telegram->sendMessage([
            'chat_id' => $message->from->id,
            'text' => \Yii::t('telegram', 'message_menu'),
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => $isHoursIsset ? \Yii::t('telegram', 'command_stop_day') : \Yii::t('telegram', 'command_start_day'), 'callback_data' => $isHoursIsset ? '/inline_stop_day' : '/inline_start_day']],
//                    [['text' => \Yii::t('telegram', 'command_stop_day'), 'callback_data' => '/inline_stop_day']],
                    [['text' => \Yii::t('telegram', 'command_orders_list'), 'callback_data' => '/order_list']],
                    [['text' => \Yii::t('telegram', 'command_orders_my'), 'callback_data' => '/my']]
                ]
            ])
        ]);
    }
}
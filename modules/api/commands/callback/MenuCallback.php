<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\CommandInterface;

class MenuCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $telegram = \Yii::$app->telegram;
        $query = $telegram->input->callback_query;
        $user = \app\models\User::findByChatId($query->from['id']);
        $keyboard = [];
        $hour = \app\models\Hours::find()->where(['and', ['user_id' => $user->id], ['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')]])->one();
        $isHoursIsset = isset( $hour );
        $text = "";

        if ($user->can('director')) {
            $keyboard = [
                [['text' => \Yii::t('telegram', 'command_orders_my'), 'callback_data' => '/my']],
                [['text' => \Yii::t('telegram', 'command_coworker_my'), 'callback_data' => '/my_coworkers']],
            ];
        } else if ($user->can('employee')) {
            $state = \app\models\Hours::checkState($user->id, time());
            switch($state) {
                case 'opened':
                    $text = "\n\n<b>".\Yii::t('telegram', 'command_workday_is_started')."</b>";
                    $keyboard[] = [['text' => \Yii::t('telegram', 'command_stop_day'), 'callback_data' => '/inline_stop_day']];
                    break;
                default:
                    $text = "\n\n<b>".\Yii::t('telegram', 'command_workday_is_not_started')."</b>";
                    $keyboard[] = [['text' => \Yii::t('telegram', 'command_start_day'), 'callback_data' => '/inline_start_day']];
                    break;
            }
            $keyboard[] = [['text' => \Yii::t('telegram', 'command_orders_list'), 'callback_data' => '/order_list']];
            $keyboard[] = [['text' => \Yii::t('telegram', 'command_orders_my'), 'callback_data' => '/my']];
        }
        $telegram->editMessageText([
            'chat_id' => $query->from['id'],
            'message_id' => $query->message['message_id'],
            'text' => \Yii::t('telegram', 'message_menu').$text,
            'parse_mode' => 'html',
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ]);
    }
}
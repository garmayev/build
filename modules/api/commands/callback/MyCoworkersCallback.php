<?php

namespace app\modules\api\commands\callback;

use app\modules\api\commands\CommandInterface;

class MyCoworkersCallback extends BaseCallback implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $telegram = \Yii::$app->telegram;
        $query = $telegram->input->callback_query;
        $user = \app\models\User::findByChatId($query->from["id"]);
        $keyboard = [];
        $text = \Yii::t('telegram', 'command_empty');
        if ($user->can("director")) {
            $text = \Yii::t("telegram", 'command_coworker_my');
            foreach ($user->referrals as $referral) {
                $keyboard[] = [[ 'text' => $referral->fullName, 'callback_data' => '/cwoorker_detail id=' . $referral->id ]];
            }
        }
        $keyboard[] = [['text' => \Yii::t('telegram', 'button_back'), 'callback_data' => '/menu']];

        $telegram->editMessageText([
            'chat_id' => $query->from['id'],
            'message_id' => $query->message['message_id'],
            'text' => $text,
            'reply_markup' => json_encode([
                'inline_keyboard' => $keyboard
            ])
        ]);
    }
}
<?php

namespace app\modules\api\commands;

use app\models\User;
use app\modules\api\commands\command\BaseCommand;

class ContactHandler extends BaseCommand implements CommandInterface
{

    public function handle($telegram, $args)
    {
        $contact = $telegram->input->message->contact;
        $phone = preg_replace("/[\(\)\+\ \-]/", "", $contact["phone_number"]);
        $model = User::find()->joinWith('profile')->where(['or', ['profile.phone' => $phone], ['user.username' => $phone]])->one();

        if ($model) {
            \Yii::error("Model founded id = {$model->id}");

            $profile = $model->profile;

            $profile->chat_id = "{$telegram->input->message->from->id}";
            $profile->family = $telegram->input->message->from->first_name;
            $profile->name = $telegram->input->message->from->last_name;
            $profile->phone = $contact["phone_number"];
            if ($profile->save()) {
                $telegram->sendMessage([
                    'chat_id' => $telegram->input->message->from->id,
                    'text' => \Yii::t('telegram', 'command_contact_complete'),
                    'reply_markup' => json_encode([
                        'keyboard' => []
                    ])
                ]);
            }
        } else {
            $telegram->sendMessage([
                'chat_id' => $telegram->input->message->from->id,
                'text' => \Yii::t('telegram', 'command_contact_not_found'),
            ]);
        }
    }
}
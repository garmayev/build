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
            return ;
        }

        if ($model->can("director")) {
            $this->setCommand([
                "commands" => [
                    ["command" => "menu", "description" => \Yii::t("telegram", "command_description_menu")],
                    ["command" => "my", "description" => \Yii::t("telegram", "command_description_my")],
                ]
            ]);
        } else {
            $this->setCommand([
                "commands" => [
                    ["command" => "menu", "description" => \Yii::t("telegram", "command_description_menu")],
                    ["command" => "start_day", "description" => \Yii::t("telegram", "command_description_start_day")],
                ]
            ]);
        }
    }

    private function setCommand($commands)
    {
        $botToken = \Yii::$app->telegram->botToken;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$botToken}/setMyCommands");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($commands));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        // Обработка ответа
        $result = json_decode($response, true);
        if ($result['ok'] ?? false) {
            \Yii::error("Команды успешно установлены!");
            \Yii::error("Установленные команды:");
            foreach ($commands["commands"] as $cmd) {
                \Yii::error("/{$cmd['command']} - {$cmd['description']}");
            }
        } else {
            \Yii::error("Ошибка при установке команд:");
            \Yii::error(print_r($result));
        }
    }
}
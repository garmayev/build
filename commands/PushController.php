<?php
namespace app\commands;

use app\models\User;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;

class PushController extends \yii\console\Controller {
    public function actionSend($user_id, $text) {
        $user = User::findOne($user_id);
        $message = (new ExpoMessage([
            'title' => 'initial title',
            'body' => 'initial body',
        ]))
            ->setTitle('This title overrides initial title')
            ->setBody('This notification body overrides initial body')
            ->setTo($user->profile->device_id)
            ->setData(['id' => 1])
            ->setChannelId('default')
            ->setBadge(0)
            ->playSound();
        (new Expo)->send($message)->push();
    }
}

?>
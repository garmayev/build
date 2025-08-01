<?php
namespace app\commands;

use app\models\User;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;

class PushController extends \yii\console\Controller {
    public function actionSend($user_id) {
        $user = User::findOne($user_id);
        echo $user->profile->device_id . "\n";
        $message = (new ExpoMessage([
            'title' => 'initial title',
            'body' => 'initial body',
        ]))
            ->setTitle('This title overrides initial title')
            ->setBody('This notification body overrides initial body')
            ->setTo($user->profile->device_id)
            ->setData(['url' => 'build://amgcompany.ru/--/order/18', 'id' => 18])
            ->setChannelId('new-order')
            ->setCategoryId('new-order')
            ->setBadge(0)
            ->playSound();
        (new Expo)->send($message)->push();
    }

    public function actionInfo($ticketId)
    {
        $expo = new Expo();
        $response = $expo->getReceipt($ticketId);
        $data = $response->getData();
        \Yii::error($data);
    }
}

?>
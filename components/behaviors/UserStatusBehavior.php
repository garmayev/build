<?php

namespace app\components\behaviors;

use app\models\User;
use yii\base\Behavior;

class UserStatusBehavior extends Behavior
{
    public function events()
    {
        return [
            \yii\web\Controller::EVENT_BEFORE_ACTION => 'checkUserStatus',
        ];
    }

    public function checkUserStatus()
    {
        if (\Yii::$app->user->isGuest) {
            return;
        }
        // Получаем текущего пользователя
        $user = \Yii::$app->user->identity;
        // Проверяем статус пользователя. Предположим, что активный статус равен 1.
        if ($user->status != User::STATUS_ACTIVE) {
            // Если статус неактивен, разлогиниваем
            \Yii::$app->user->logout();
            // Перенаправляем на главную страницу или страницу с сообщением
            \Yii::$app->getResponse()->redirect(['/user/login'])->send();
            exit; // Важно завершить выполнение, чтобы действие не продолжалось
        }
    }
}
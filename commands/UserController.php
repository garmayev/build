<?php

namespace app\commands;

use app\models\forms\UserRegisterForm;
use app\models\User;
use Yii;
use yii\console\Controller;

class UserController extends Controller
{
    public function actionCreate($username, $email)
    {
        $password = Yii::$app->security->generateRandomString(8);
        $model = new User([
            'username' => $username,
            'email' => $email,
            'auth_key' => Yii::$app->security->generateRandomString(16),
            'access_token' => Yii::$app->security->generateRandomString(16),
            'password_hash' => Yii::$app->security->generatePasswordHash($password),
            'profile' => [

            ]
        ]);
        if ($model->validate() && $model->save()) {
            echo \Yii::t('app', 'Your password: {password}', ['password' => $password]);
            echo "\n";
            exit(\Yii::t('app', 'Registered successfully.'));
        } else {
            \Yii::error($model->getErrors());
            exit(\Yii::t('app', 'Registration failed.'));
        }
    }

    public function actionPassword($username, $password)
    {
        $model = User::findByUsername($username);
        if ($model) {
            $model->password_hash = \Yii::$app->security->generatePasswordHash($password);
            if ($model->save()) {
                exit(\Yii::t('app', 'Password successfully changed.'));
            } else {
                \Yii::error($model->getErrors());
                exit(\Yii::t('app', 'Password changed is failed.'));
            }
        }
        exit(\Yii::t('app', 'User not found.'));
    }

    public function actionSetStatus($username, $status)
    {
        $model = User::findByUsername($username);
        if ($model) {
            $model->status = $status;
            if ($model->save()) {
                exit(\Yii::t('app', 'Status successfully changed.'));
            }
        }
        exit(\Yii::t('app', 'User not found.'));
    }

    public function actionGetSuitable($username)
    {
        $model = User::findByUsername($username);
        foreach ($model->getSuitableOrders() as $order) {
            echo "Order #{$order->id}\n";
        }
    }
}
<?php

namespace app\commands;

use app\models\forms\UserRegisterForm;
use app\models\User;
use yii\console\Controller;

class UserController extends Controller
{
    public function actionCreate($username, $email, $password)
    {
        $model = new UserRegisterForm([
            'username' => $username,
            'email' => $email,
            'new_password' => $password,
            'referrer' => method_exists(\Yii::$app, 'user') ? \Yii::$app->user->getId() : 1,
        ]);
        if ($model->validate() && $model->register()) {
            exit(\Yii::t('app', 'Registered successfully.'));
        } else {
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
}
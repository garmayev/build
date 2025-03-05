<?php

namespace app\commands;

use app\models\User;
use yii\console\Controller;

class UserController extends Controller
{
    public function actionCreate($username, $email, $password = null)
    {
        if (is_null($password)) {
            $password = \Yii::$app->security->generateRandomString(8);
        }
        $model = new User([
            'username' => $username,
            'email' => $email,
            'password_hash' => \Yii::$app->security->generatePasswordHash($password),
            'auth_key' => \Yii::$app->security->generateRandomString(),
            'access_token' => \Yii::$app->security->generateRandomString(),
        ]);
        if ( $model->save() ) {
            $this->stdout("\nUser '$username' with email $email created identified by $password\n");
            return null;
        } else {
            $this->stdout("\n".json_encode($model->getErrorSummary(true)));
        }
        return null;
    }

    public function actionPassword($username, $password)
    {
        $model = User::findOne(['username' => $username]);
        $model->password_hash = \Yii::$app->security->generatePasswordHash($password);
        if ($model->save()) {
            $this->stdout("\nUser '$username' password is changed");
        } else {
            $this->stdout("\nPassword is not changed");
        }
        return null;
    }
}
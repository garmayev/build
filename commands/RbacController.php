<?php

namespace app\commands;

use app\models\User;
use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll();

        $employee = $auth->createRole('employee');
        $auth->add($employee);

        $director = $auth->createRole('director');
        $auth->add($director);

        $admin = $auth->createRole('admin');
        $auth->add($admin);
    }

    public function actionAssign($username, $role)
    {
        $auth = Yii::$app->authManager;

        $role = $auth->getRole($role);
        $model = User::findOne(['username' => $username]);
        $auth->revokeAll($model->id);
        $auth->assign($role, $model->id);
    }
}
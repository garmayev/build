<?php

namespace app\commands;

use app\modules\user\models\User;
use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        $employee = $auth->createRole('employee');
        $auth->add($employee);

        $director = $auth->createRole('director');
        $auth->add($director);
        $auth->addChild($director, $employee);

        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $director);
        $auth->addChild($admin, $employee);
    }

    public function actionAssign($username, $role)
    {
        $auth = Yii::$app->authManager;

        $role = $auth->getRole($role);
        $model = User::findOne(['username' => $username]);

        $auth->assign($role, $model->id);
    }
}
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

    public function actionAssign($username, $roleName)
    {
        $auth = Yii::$app->authManager;

        $role = $auth->getRole($roleName);
        if (empty($role)) {
            echo \Yii::t('app', 'missing_role {role}', ['role' => $roleName])."\n";
            echo \Yii::t('app', 'available_roles').":\n";
            foreach ($auth->getRoles() as $role) {
                echo "\t".$role->name."\n";
            }
        }
        $model = User::findOne(['username' => $username]);
        $auth->revokeAll($model->id);
        $auth->assign($role, $model->id);
    }
}
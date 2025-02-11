<?php

namespace app\modules\api\controllers;

use app\models\Coworker;
use yii\rest\ActiveController;

class CoworkerController extends ActiveController
{
    public $modelClass = Coworker::class;

    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge(
            parent::behaviors(),
            [
                'corsFilter' => [ 
                    'class' => \yii\filters\Cors::class,
                    'cors' => [
                        'Origin' => ['*'],
                        'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS', 'PREFLIGHT'],
                        'Access-Control-Request-Headers' => ['*'],
                        'Access-Control-Allow-Credentials' => false,
                        'Access-Control-Max-Age' => 86400,
                        'Access-Control-Allow-Origin' => ['*'],
                    ],
                ],
                'authenticator' => [
                    'class' => \yii\filters\auth\HttpBearerAuth::class,
                ]
            ]
        );
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    public function actionIndex()
    {
        if (\Yii::$app->user->isGuest) {
            return ["ok" => false, "message" => "Unknown user"];
        }
        return ["ok" => true, "data" => Coworker::find()->where(['user_id' => \Yii::$app->user->getId()])->all()];
    }

}
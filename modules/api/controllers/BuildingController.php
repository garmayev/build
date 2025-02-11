<?php

namespace app\modules\api\controllers;

use app\models\Building;
use yii\rest\ActiveController;

class BuildingController extends ActiveController
{
    public $modelClass = Building::class;

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
//        unset($actions['view']);
        return $actions;
    }

    public function actionIndex()
    {
        if (\Yii::$app->user->isGuest) {
            return ["ok" => false, "message" => "Unknown user"];
        }
        return ["ok" => true, "data" => Building::find()->where(['user_id' => \Yii::$app->user->getId()])->all()];
    }
}
<?php

namespace app\modules\api\controllers;

use app\models\Building;
use yii\rest\ActiveController;

class BuildingController extends ActiveController
{
    public $modelClass = Building::class;
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'data',
    ];

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
                ],
                'contentNegotiator' => [
                    'class' => \yii\filters\ContentNegotiator::class,
                    'formats' => [
                        'application/json' => \yii\web\Response::FORMAT_JSON,
                    ]
                ]
            ]
        );
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['dataFilter'] = [
            'class' => \yii\data\ActiveDataFilter::class,
            'searchModel' => $this->modelClass,
        ];
        return $actions;
    }

    public function actionList()
    {
        return ["data" => Building::find()->where(['user_id' => \Yii::$app->user->getId()])->all()];
    }
}
<?php

namespace app\modules\api\controllers;

use app\models\Coworker;
//use app\models\search\CoworkerSearch;
use yii\rest\ActiveController;

class CoworkerController extends ActiveController
{
    public $modelClass = Coworker::class;
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
                    'except' => ['options', 'images'],
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

/*
    public function actionIndex()
    {
        if (\Yii::$app->user->isGuest) {
            return ["ok" => false, "message" => "Unknown user"];
        }
        return ["ok" => true, "data" => Coworker::find()->where(['user_id' => \Yii::$app->user->getId()])->all()];
    } 
*/
    public function actionDetails($id)
    {
        $model = Coworker::findOne($id);
        return ["ok" => true, "data" => $model];
    }

    public function actionImages() 
    {
        \Yii::error("check?");
        $files = $_FILES;
        $target_path = "/upload/".basename($files['file']['name']);

        if (move_uploaded_file($files['file']['tmp_name'], \Yii::getAlias("@webroot").$target_path)) {
            return ["ok" => true, "data" => $target_path];
        } else {
            \Yii::error("Something went wrong");
            return ["ok" => false];
        }
    }

    public function actionSearch($text)
    {
        $models = Coworker::find()
            ->where(['user_id' => \Yii::$app->user->getId()])
            ->andWhere(['or', ['like', 'firstname', $text], ['like', 'lastname', $text], ['like', 'email', $text], ['like', 'phone', $text]]);

        return ["ok" => true, "data" => $models->all()];
    }
}
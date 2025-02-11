<?php

namespace app\modules\api\controllers;

use app\models\Order;
use yii\rest\Controller;
use yii\rest\ActiveController;

class OrderController extends ActiveController
{
    public $modelClass = Order::class;

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
                        'Access-Control-Allow-Credentials' => true,
                        'Access-Control-Allow-Origin' => ['*'],
                    ],
                ],
                'authenticator' => [
                    'class' => \yii\filters\auth\HttpBearerAuth::class,
                    'except' => ['detail']
                ]
            ]
        );
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
    }

    public function actionIndex()
    {
//        \Yii::error( \Yii::$app->user->identity->getId() );
        if (\Yii::$app->user->isGuest) {
            return ["ok" => false, "message" => "Unknown user"];
        }
        return ["ok" => true, "data" => Order::find()->where(['created_by' => \Yii::$app->user->getId()])->all(), "user_id" => \Yii::$app->user->identity->getId()];
    }

    public function actionView($id)
    {
        if (\Yii::$app->user->isGuest) {
            return ["ok" => false, "message" => "Unknown user"];
        }
        return ["ok" => true, "data" => Order::findOne($id)];
    }

    public function actionDetail($id)
    {
        if ($id) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $model = Order::findOne($id);
            if (\Yii::$app->user->isGuest) {
                return ["ok" => false, "message" => "Unknown user"];
            }
            return ["ok" => true, "data" => $model->getDetails()];
        }
        return [];
    }

    public function actionImages() 
    {
//        \Yii::error("Test");
    }

    public function actionDelete($id) {
        $model = Order::findOne($id);
        return ["ok" => $model->delete()];
    }
}
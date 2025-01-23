<?php

namespace app\modules\api\controllers;

use app\models\Building;
use yii\rest\Controller;

class BuildingController extends Controller
{
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
                        'Access-Control-Allow-Credentials' => null,
                        'Access-Control-Max-Age' => 86400,
                        'Access-Control-Allow-Origin' => ['*'],
                    ],
                ],
                'access' => [
                    'class' => \yii\filters\AccessControl::class,
                    'rules' => [
                        [ 'allow' => true, 'roles' => ['?'], 'actions' => ['index', 'view'] ],
                        [ 'allow' => true, 'roles' => ['@'], 'actions' => ['options', 'preflight', 'view'] ],
                    ],
                ],
                'authenticator' => [
                    'class' => \yii\filters\auth\CompositeAuth::class,
                    'authMethods' => [
                        \yii\filters\auth\HttpBearerAuth::class,
                        \yii\filters\auth\HttpBasicAuth::class,
                        \yii\filters\auth\QueryParamAuth::class,
                    ],
                    'except' => ['OPTIONS', 'PREFLIGHT', 'index', 'view']
                ]
            ]
        );
    }

    public function actionIndex() 
    {
        return Building::find()->all();
    }

    public function actionView($id)
    {
        return Building::findOne($id);
    }
}
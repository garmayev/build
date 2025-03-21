<?php

namespace app\modules\api\controllers;

use yii\rest\ActiveController;

/**
 * Default controller for the `api` module
 */
class DefaultController extends ActiveController
{
    public $modelClass = \app\models\Order::class;

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'data',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['corsFilter'] = [ 
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD', 'PREFLIGHT'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Allow-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD', 'PREFLIGHT'],
                'Access-Control-Allow-Origin' => ['*'],
            ],
        ];
        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'HEAD', 'OPTIONS'],
            'update' => ['POST', 'PUT', 'PATCH', 'HEAD', 'OPTIONS'],
            'delete' => ['POST', 'DELETE', 'HEAD', 'OPTIONS']
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
//        $action['options'] = [
//            'class' => \yii\rest\OptionAction::class
//        ];
        return $actions;
    }

    public function beforeAction($action)
    {
        \Yii::$app->language = "ru-RU";
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionOptions()
    {
        if (\Yii::$app->getRequest()->getMethod() !== 'OPTIONS') {
            \Yii::$app->getResponse()->setStatusCode(405);
        }
        \Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS', 'PREFLIGHT']));
        \Yii::$app->getResponse()->getHeaders()->set('Access-Control-Access-Method', implode(', ', ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS', 'PREFLIGHT']));
    }
}

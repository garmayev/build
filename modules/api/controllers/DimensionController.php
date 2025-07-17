<?php

namespace app\modules\api\controllers;

use app\models\Category;
use app\models\Dimension;
use app\models\Property;
use yii\rest\Controller;

class DimensionController extends Controller
{
    public $modelClass = Dimension::class;
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    'Origin' => ['http://localhost:3000', 'http://build.local', 'https://build.amgcompany.ru'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'PREFLIGHT'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Allow-Origin' => ['*'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    // Guests
                    [ 'allow' => true, 'roles' => ['?'], 'actions' => [] ],
                    // Users
                    [ 'allow' => true, 'roles' => ['@'], 'actions' => ['index', 'list', 'view', 'info'] ],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['index', 'list', 'view'],
            ],
        ];
    }

    protected function verbs()
    {
        return [
            'index' => ['GET', 'OPTIONS'],
            'view' => ['GET', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['POST', 'PUT', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
            'check' => ['POST', 'OPTIONS'],
            'login' => ['POST', 'OPTIONS'],
            'calendar-month' => ['GET', 'OPTIONS'],
        ];
    }

    public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return [
            'ok' => true,
            'data' => $this->modelClass::find()->all()
        ];
    }

    public function actionView($id)
    {
        return $this->modelClass::findOne($id);
    }

    public function actionByProperty()
    {
        if ( \Yii::$app->request->isPost && isset($_POST['depdrop_all_params']) ) {
            $id = $_POST['depdrop_all_params']['property_id'];
            $property = Property::findOne($id);
            $result = [];
            if ($property) {
                foreach ($property->dimensions as $key => $dimension) {
                    $result[] = ["id" => $dimension->id, "name" => $dimension->title];
                }
                return ['output' => $result, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }
}

<?php

namespace app\modules\api\controllers;

use app\models\Category;
use app\models\Property;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;

class PropertyController extends Controller
{
    public $modelClass = Property::class;

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
            'data' => $this->modelClass::find()->all(),
        ];
    }

    public function actionView($id)
    {
        return $this->modelClass::findOne($id);
    }

    public function actionByCategory($id = null)
    {
        if (\Yii::$app->request->isPost && isset($_POST['depdrop_all_params'])) {
            $category_id = $_POST['depdrop_all_params']['category_id'];
            $category = Category::findOne($category_id);
            $result = [];
            foreach ($category->properties as $key => $property) {
                $result[] = ["id" => $property->id, "name" => $property->title];
            }
            return ['output' => $result, 'selected' => ''];
        }
        $category = Category::findOne($id);
        return ['ok' => true, 'results' => $category->properties];
    }
}

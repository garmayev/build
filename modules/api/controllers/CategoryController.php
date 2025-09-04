<?php

namespace app\modules\api\controllers;

use app\models\Category;
use yii\rest\ActiveController;

class CategoryController extends ActiveController
{
    public $modelClass = Category::class;

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'data',
    ];

    public function behaviors()
    {
/*        return [
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
        ]; */

        $behaviors = parent::behaviors();

        // Убираем стандартный authenticator (добавим его позже)
        unset($behaviors['authenticator']);

        // Настраиваем CORS фильтр первым
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
//                'Origin' => ['http://localhost:3000', 'http://build.local', 'https://build.amgcompany.ru'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
                'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page', 'X-Pagination-Page-Count'],
            ],
        ];

        // Добавляем аутентификатор ПОСЛЕ CORS
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['options'], // Добавляем options и calendar в исключения
        ];

        // Настройка контроля доступа
        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'actions' => ['index', 'list', 'view', 'info'],
                ],
            ],
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'index' => ['GET', 'POST', 'OPTIONS'],
            'view' => ['GET', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['POST', 'PUT', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
            'check' => ['POST', 'OPTIONS'],
            'login' => ['POST', 'OPTIONS'],
            'calendar-month' => ['GET', 'OPTIONS'],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        $actions['options'] = [
            'class' => \yii\rest\OptionAction::class
        ];
        return $actions;
    }

    public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionIndex($type = 1)
    {
        return ['results' => Category::find()->where(['type' => $type])->all()];
    }

    public function actionList()
    {
        return ['ok' => true, 'data' => Category::find()->all()];
    }
    public function actionView($id)
    {
        return Category::findOne($id);
    }
}

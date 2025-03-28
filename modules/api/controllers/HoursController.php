<?php
namespace app\modules\api\controllers;

class HoursController extends \yii\rest\Controller {
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'data',
    ];

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'PREFLIGHT'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => false,
                    'Access-Control-Max-Age' => 86400,
                    'Access-Control-Allow-Origin' => ['*'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    // Guests
                    [ 'allow' => true, 'roles' => ['@'], 'actions' => ['images', 'status', 'create'] ],
                    // Users
                    [ 'allow' => true, 'roles' => ['?'], 'actions' => ['index', 'view', 'update', 'create', 'delete', 'set-hours', 'close', 'detail', 'by-coworker'] ],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['OPTIONS', 'PREFLIGHT', 'HEAD', 'images', 'status', 'create']
            ],
        ];
    }

    protected function verbs()
    {
        return [
            'detail' => ['GET', 'OPTIONS'],
            'view' => ['GET', 'OPTIONS'],
            'status' => ['GET', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['POST', 'PUT', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
            'check' => ['POST', 'OPTIONS'],
            'login' => ['POST', 'OPTIONS'],
        ];
    }

    public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actions()
    {
        $actions = parent::actions();
//        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['options'] = [
            'class' => \yii\rest\OptionAction::class
        ];
        return $actions;
    }

    public function actionCreate($time, $coworker_id, $is_payed, $count)
    {
//        $order = \app\models\Order::find()->where(['id' => $order_id])->one();
        $coworker = \app\models\Coworker::findOne($coworker_id);
        \Yii::error($time);
        \Yii::error(time());
        \Yii::error(date('Y-m-d', $time));
        if (isset($coworker)) {
            $hours = \app\models\Hours::find()->where(['coworker_id' => $coworker->id])->andWhere(['date' => date('Y-m-d', $time)])->one();
            if (isset($hours)) {
                $result = $hours->load(['Hours' => ['is_payed' => $is_payed, 'count' => $count]]) && $hours->save();
                if (!$result) { \Yii::error($hours->getErrorSummary(true)); }
                return ['ok' => $result];
            } else {
                $hours = new \app\models\Hours(['coworker_id' => $coworker->id, 'date' => date('Y-m-d', $time), 'count' => $count, 'is_payed' => $is_payed]);
            }
            $is_saved = $hours->save();
            return ['ok' => $is_saved, 'message' => $hours->getErrorSummary(true)];
        }
        return ['ok' => false];
    }
}
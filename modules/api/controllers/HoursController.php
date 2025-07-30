<?php
namespace app\modules\api\controllers;

use app\models\Hours;

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
                    [ 'allow' => true, 'roles' => ['@'], 'actions' => ['images', 'status', 'create', 'get-hours'] ],
                    // Users
                    [ 'allow' => true, 'roles' => ['?'], 'actions' => ['index', 'view', 'update', 'create', 'delete', 'set-hours', 'get-hours', 'close', 'detail', 'by-coworker', 'check-today'] ],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['OPTIONS', 'PREFLIGHT', 'HEAD', 'images', 'status', 'get-hours']
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
            'check-today' => ['GET', 'OPTIONS'],
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

    public function actionCreate($time, $coworker_id, $is_payed, $count, $order_id = null)
    {
        $order = \app\models\Order::find()->where(['id' => $order_id])->one();
        $coworker = \app\models\User::findOne(\Yii::$app->user->getId());
        \Yii::error( \Yii::$app->user->isGuest );
        if (isset($coworker)) {
            $hours = \app\models\Hours::find()->where(['user_id' => $coworker->id])->andWhere(['date' => date('Y-m-d', $time)])->one();
            if (isset($hours)) {
                $result = $hours->load(['Hours' => ['is_payed' => $is_payed, 'count' => $count, 'order_id' => $order_id]]) && $hours->save();
                if (!$result) { \Yii::error($hours->getErrorSummary(true)); }
                return ['ok' => $result];
            } else {
                $hours = new \app\models\Hours(['user_id' => $coworker->id, 'date' => date('Y-m-d', $time), 'count' => $count, 'is_payed' => $is_payed, 'order_id' => $order_id]);
            }
            $is_saved = $hours->save();
            return ['ok' => $is_saved, 'message' => $hours->getErrorSummary(true)];
        }
        return ['ok' => false];
    }

    public function actionGetHours($coworker_id, $date)
    {
        $d = \Yii::$app->formatter->asDate($date, 'php:YYYY-m-d');
        if ($coworker_id) {
            return ["ok" => true, "model" => \app\models\Hours::find()->where(["date" => $d])->andWhere(["user_id" => $coworker_id])->one()];
        }
    }

    public function actionCheckToday()
    {
        $hours = \app\models\Hours::find()->where(['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')])->andWhere(['user_id' => \Yii::$app->user->getId()])->one();
        return $hours;
    }
}
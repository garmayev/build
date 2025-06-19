<?php

namespace app\modules\api\controllers;

use app\models\Coworker;
use app\models\Hours;
use app\models\User;
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
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::class,
                'cors' => [
                    'Origin' => ['http://localhost:3000', 'http://build.local', 'https://build.amgcompany.ru'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'PREFLIGHT'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'CORS_ORIGIN_WHITELIST' => '',
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
                    [ 'allow' => true, 'roles' => ['@'], 'actions' => ['check', 'list', 'view', 'create', 'suitableOrders', 'calendar-month'] ],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
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

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['index']['dataFilter'] = [
            'class' => \yii\data\ActiveDataFilter::class,
            'searchModel' => $this->modelClass,
        ];
        return $actions;
    }

    public function prepareDataProvider()
    {
        return new \yii\data\ActiveDataProvider([
            'query' => \app\models\Coworker::find()->where(['created_by' => \Yii::$app->user->getId()])
        ]);
    }

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

    public function actionSuitableOrders()
    {
        $coworker = Coworker::findOne(['user_id' => \Yii::$app->user->getId()]);

        if (!$coworker) {
            return ["ok" => false, "message" => \Yii::t("app", "Coworker not found")];
        }

        $orders = \app\models\Order::find()
            ->where(['status' => \app\models\Order::STATUS_NEW])
            ->orderBy(['id' => SORT_DESC])
            ->all();
        $suitableOrders = [];

        foreach ($orders as $order) {
            foreach ( $order->getSuitableCoworkers($order->priority_level) as $item ) {
                if ($coworker->id === $item->id) {
                    $suitableOrders[] = $order;
                }
            }
        }

        return ["ok" => true, "data" => $suitableOrders];
    }

    public function actionInviteRequest($order_id)
    {
        $order = \app\models\Order::findOne($order_id);
        $order->notify();
    }

    public function actionCalendarMonth($year, $month)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $result = [];
        $users = \app\models\User::find()->all();
        /**
         * @var User $user
         */
        foreach ($users as $user) {
            $hours = \app\models\Hours::find()
                ->where(['>=', 'date', date("$year-$month-01")])
                ->andWhere(['<=', 'date', date("$year-$month-".cal_days_in_month(CAL_GREGORIAN, $month, $year))])
                ->andWhere(['coworker_id' => $user->id])
                ->all();
            $payed = 0;
            $payed_hours = 0;
            $un_payed = 0;
            $un_payed_hours = 0;
            /**
             * @var Hours $hour
             * @var int $payed
             * @var int $un_payed
             * @var int $payed_hours
             * @var int $un_payed_hours
             */
            foreach ($hours as $hour) {
                if ($hour->is_payed) {
                    $payed += $hour->price;
                    $payed_hours += $hour->count;
                } else {
                    $un_payed += $hour->price;
                    $un_payed_hours += $hour->count;
                }
            }
            $result[] = [
                'id' => $user->id,
                'name' => $user->name,
                'data' => $hours,
                'total' => 0,
                'payed' => $payed,
                'payed_hours' => $payed_hours,
                'un_payed' => $un_payed,
                'un_payed_hours' => $un_payed_hours,
            ];
        }
        return $result;
    }
}
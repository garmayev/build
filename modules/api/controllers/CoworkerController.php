<?php

namespace app\modules\api\controllers;

use app\models\Hours;
use app\models\Order;
use app\models\OrderUser;
use app\models\User;
use yii\rest\ActiveController;

class CoworkerController extends ActiveController
{
    public $modelClass = User::class;

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'data',
    ];

    public function behaviors()
    {
        /* return [
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
                    ['allow' => true, 'roles' => ['?'], 'actions' => ['advanced']],
                    // Users
                    ['allow' => true, 'roles' => ['@'], 'actions' => ['check', 'list', 'view', 'create', 'suitable-orders', 'calendar-month', 'calendar', 'advanced']],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['calendar']
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
                    'actions' => ['index', 'check', 'list', 'view', 'create', 'suitable-orders', 'calendar-month', 'advanced', 'calendar'],
                ],
            ],
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'index' => ['GET', 'OPTIONS'],
            'view' => ['GET', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['POST', 'PUT', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
            'calendar' => ['GET', 'POST', 'OPTIONS'],
            'calendar-month' => ['GET', 'OPTIONS'],
            'advanced' => ['POST', 'OPTIONS'],
            'suitable-orders' => ['POST', 'GET', 'OPTIONS']
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
        $actions['options'] = [
            'class' => \yii\rest\OptionAction::class
        ];
        return $actions;
    }

    public function prepareDataProvider()
    {
        return new \yii\data\ActiveDataProvider([
            'query' => \app\models\User::find()->where(['referrer_id' => \Yii::$app->user->getId()])->orWhere(['priority_level' => 0])
        ]);
    }

    public function actionDetails($id)
    {
        $model = User::findOne($id);
        return ["ok" => true, "data" => $model];
    }

    public function actionImages()
    {
//        \Yii::error("check?");
        $files = $_FILES;
        $target_path = "/upload/" . basename($files['file']['name']);

        if (move_uploaded_file($files['file']['tmp_name'], \Yii::getAlias("@webroot") . $target_path)) {
            return ["ok" => true, "data" => $target_path];
        } else {
            \Yii::error("Something went wrong");
            return ["ok" => false];
        }
    }

    public function actionSearch($text)
    {
        $models = User::find()
            ->where(['referrer_id' => \Yii::$app->user->getId()])
            ->andWhere(['or',
                ['like', 'fullName', $text],
                ['like', 'email', $text],
                ['like', 'phone', $text]
            ]);

        return ["ok" => true, "data" => $models->all()];
    }

    public function actionSuitableOrders()
    {
        \Yii::error( \Yii::$app->user->getId() );
        $coworker = User::findOne(\Yii::$app->user->getId());
        if ($coworker) {
            return ["ok" => true, "data" => $coworker->getSuitableOrders()];
        }
        return ["ok" => false];
    }

    public function actionInviteRequest($order_id)
    {
        $order = \app\models\Order::findOne($order_id);
        $order->notify();
    }

    public function actionCalendar($year, $month)
    {
        $user = \Yii::$app->user->identity;
        $referrals = $user->referrals;
        $result = [];
        $startDate = date("$year-$month-01");
        $finishDate = date("$year-$month-".cal_days_in_month(CAL_GREGORIAN, $month, $year));
        foreach ($referrals as $referral) {
            $result[] = [
                'user' => $referral,
                'hours' => $referral->getHoursByMonth($startDate, $finishDate),
                'debit_amount' => $referral->getDebitAmount($startDate, $finishDate),
                'credit_amount' => $referral->getCreditAmount($startDate, $finishDate),
                'debit_hours' => $referral->getDebitHours($startDate, $finishDate),
                'credit_hours' => $referral->getCreditHours($startDate, $finishDate),
            ];
        }
        return $result;
    }

    public function actionCalendarMonth($year, $month)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $result = [];
        if (\Yii::$app->user->can('admin')) {
            $users = \app\models\User::find()->where(['referrer_id' => \Yii::$app->user->id])->all();
        } else if (\Yii::$app->user->can('director')) {
            $users = \app\models\User::find()->where(['referrer_id' => \Yii::$app->user->id])->all();
        } else {
            $users = \app\models\User::find()->where(['id' => \Yii::$app->user->id])->all();
        }
        /**
         * @var User $user
         */
        $referrals = \Yii::$app->user->identity->referrals;
        $orderIds = OrderUser::find()
            ->select('order_id')
            ->innerJoin('user', 'order_user.user_id = user.id')
            ->where(['user.referrer_id' => \Yii::$app->user->id])
            ->column();
        $orders = Order::find()
            ->where(['id' => $orderIds])
            ->all();
        $hours = Hours::find()
            ->where(['order_id' => $orderIds])
            ->all();
//        foreach ($users as $user) {
//            $hours = \app\models\Hours::find()
//                ->where(['>=', 'date', date("$year-$month-01")])
//                ->andWhere(['<=', 'date', date("$year-$month-".cal_days_in_month(CAL_GREGORIAN, $month, $year))])
//                ->andWhere(['coworker_id' => $user->id])
//                ->all();
//            $payed = 0;
//            $payed_hours = 0;
//            $un_payed = 0;
//            $un_payed_hours = 0;
//            /**
//             * @var Hours $hour
//             * @var int $payed
//             * @var int $un_payed
//             * @var int $payed_hours
//             * @var int $un_payed_hours
//             */
//            foreach ($hours as $hour) {
//                if ($hour->is_payed) {
//                    $payed += $hour->price;
//                    $payed_hours += $hour->count;
//                } else {
//                    $un_payed += $hour->price;
//                    $un_payed_hours += $hour->count;
//                }
//            }
//            $result[] = [
//                'id' => $user->id,
//                'name' => $user->name,
//                'data' => $hours,
//                'total' => 0,
//                'payed' => $payed,
//                'payed_hours' => $payed_hours,
//                'un_payed' => $un_payed,
//                'un_payed_hours' => $un_payed_hours,
//            ];
//        }
        return $hours;
    }

    public function actionAdvanced($id)
    {
        $model = User::findOne($id);
        try {
            if ($model->load(\Yii::$app->request->post()) && $model->save()) {
                return ['ok' => true, 'model' => $model, 'message' => \Yii::t('app', 'Advanced settings is saved')];
            }
            return ["ok" => false, 'message' => $model->errors];
        } catch (\Exception $exception) {
            \Yii::error($exception->getMessage());
            throw $exception;
        }
    }
}
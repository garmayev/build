<?php
namespace app\modules\api\controllers;

use app\models\Hours;
use yii\base\InvalidConfigException;

class HoursController extends \yii\rest\Controller {
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
                    [ 'allow' => true, 'roles' => ['@'], 'actions' => ['images', 'status', 'create', 'get-hours', 'quick'] ],
                    // Users
                    [ 'allow' => true, 'roles' => ['?'], 'actions' => ['index', 'view', 'update', 'create', 'delete', 'set-hours', 'get-hours', 'close', 'detail', 'by-coworker', 'check-today', 'close-workday', 'open-workday', 'is-opened', 'is-closed', 'by-date', 'quick'] ],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['OPTIONS', 'PREFLIGHT', 'HEAD', 'images', 'status', 'get-hours', 'quick']
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
                'Access-Control-Allow-Credentials' => null,
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
                    // Guests
                    [ 'allow' => true, 'roles' => ['?'], 'actions' => ['images', 'status', 'create', 'get-hours', 'quick'] ],
                    // Users
                    [ 'allow' => true, 'roles' => ['@'], 'actions' => ['index', 'view', 'update', 'create', 'delete', 'set-hours', 'get-hours', 'close', 'detail', 'by-coworker', 'check-today', 'close-workday', 'start-workday', 'is-opened', 'is-closed', 'by-date', 'quick'] ],
            ],
        ];

        return $behaviors;
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
            'close-workday' => ['POST', 'OPTIONS'],
            'start-workday' => ['POST', 'OPTIONS'],
            'is-opened' => ['POST', 'OPTIONS'],
            'is-closed' => ['POST', 'OPTIONS'],
            'by-date' => ['GET', 'OPTIONS'],
            'quick' => ['POST', 'OPTIONS']
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
//        \Yii::error( \Yii::$app->user->isGuest );
        if (isset($coworker)) {
            $hours = \app\models\Hours::find()->where(['user_id' => $coworker->id])->andWhere(['date' => date('Y-m-d', $time)])->one();
            if (isset($hours)) {
                $result = $hours->load(['Hours' => ['is_payed' => $is_payed, 'count' => $count, 'order_id' => $order_id]]) && $hours->save();
                if (!$result) { \Yii::error($hours->getErrorSummary(true)); }
                return ['ok' => $result];
            } else {
                $hours = new \app\models\Hours(['user_id' => $coworker->id, 'date' => date('Y-m-d', $time), 'count' => $count, 'is_payed' => $is_payed, 'order_id' => $order_id, 'start_time' => date('Y-m-d H:i:s')]);
            }
            $is_saved = $hours->save();
            return ['ok' => $is_saved, 'message' => $hours->getErrorSummary(true)];
        }
        return ['ok' => false];
    }

    /**
     * @throws InvalidConfigException
     */
    public function actionGetHours($coworker_id = null, $date = null)
    {
        $d = \Yii::$app->formatter->asDate($date, 'php:YYYY-m-d');
        if ($coworker_id) {
            return ["ok" => true, "model" => \app\models\Hours::find()->where(["date" => $d])->andWhere(["user_id" => $coworker_id])->one()];
        }
        return ["ok" => false];
    }

    public function actionCloseWorkday()
    {
        $raw = \Yii::$app->request->post();
        $user = \Yii::$app->user->identity;
        $hour = \app\models\Hours::find()
            ->where(['user_id' => $raw['user_id']])
            ->andWhere(['date' => $raw['date']])
            ->andWhere(['order_id' => $raw['order_id']])
            ->one();
        $hour->stop_time = date('Y-m-d H:i:s');
        $hour->count = ceil((\Yii::$app->formatter->asTimestamp($hour->stop_time) - \Yii::$app->formatter->asTimestamp($hour->start_time)) / 3600);
        if ($hour->save()) {
            return ['ok' => true, 'model' => $hour];
        }
        return ['ok' => false, 'message' => $hour->getErrorSummary('true')];
    }

    public function actionStartWorkday()
    {
        $raw = \Yii::$app->request->post();
        $user = \app\models\User::findOne(\Yii::$app->user->getId());

        $hours = \app\models\Hours::find()->where(['user_id' => $user->id])->andWhere(['date' => $raw['date']])->andWhere(['order_id' => $raw['order_id']])->one();
        if (isset($hours)) {
            $result = $hours->load(['Hours' => $raw]) && $hours->save();
            if (!$result) { \Yii::error($hours->getErrorSummary(true)); }
            return ['ok' => $result, 'model' => $hours];
        }

        $hours = new \app\models\Hours(array_merge(\Yii::$app->request->post(), ['start_time' => date('Y-m-d H:i:s')]));
        $is_saved = $hours->save();
        if (!$is_saved) {
            \Yii::error($hours->getErrorSummary(true));
        }
        return ['ok' => $is_saved, 'message' => $hours->getErrorSummary(true)];

    }

    public function actionCheckToday()
    {
        $hours = \app\models\Hours::find()->where(['date' => \Yii::$app->formatter->asDate(time(), 'php:Y-m-d')])->andWhere(['user_id' => \Yii::$app->user->getId()])->one();
        return $hours;
    }

    public function actionIsOpened()
    {
        $raw = \Yii::$app->request->post();
        $hours = \app\models\Hours::find()
            ->where(['user_id' => $raw['user_id']])
            ->andWhere(['date' => $raw['date']])
            ->andWhere(['order_id' => $raw['order_id']])
            ->one();
        if ($hours) {
            return ['ok' => isset($hours->start_time)];
        }
        return ['ok' => false];
    }

    public function actionIsClosed()
    {
        $raw = \Yii::$app->request->post();
        $hours = \app\models\Hours::find()->where(['user_id' => $raw['user_id']])->andWhere(['date' => $raw['date']])->andWhere(['order_id' => $raw['order_id']])->one();
        if ($hours) {
            return ['ok' => isset($hours->stop_time)];
        }
        return ['ok' => false];
    }

    public function actionByDate($date)
    {
        $hours = \app\models\Hours::find()->where(['date' => $date])->andWhere(['user_id' => \Yii::$app->user->getId()])->all();
        if ($hours) {
            return ['ok' => true, 'data' => $hours];
        }
        return ['ok' => false, 'message' => \Yii::t('app', 'empty_list')];
    }

    public function actionQuick()
    {
        $raw = json_decode(file_get_contents("php://input"), true);
        \Yii::error($raw);
        $hours = new \app\models\Hours($raw);
        return ['ok' => $hours->save(), 'message' => $hours->errors];
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
}
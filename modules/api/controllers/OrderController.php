<?php

namespace app\modules\api\controllers;

use app\models\Coworker;
use app\models\Hours;
use app\models\Order;
use app\models\telegram\TelegramMessage;
use app\models\User;
use yii\helpers\ArrayHelper;

class OrderController extends \yii\rest\ActiveController
{
    public $modelClass = Order::class;

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
                    ['allow' => true, 'roles' => ['@'], 'actions' => ['images', 'status', 'by-coworker',]],
                    ['allow' => true, 'roles' => ['?'], 'actions' => [
                        'index',
                        'view',
                        'update',
                        'create',
                        'delete',
                        'my',
                        'close',
                        'detail',
                        'by-coworker',
                        'free',
                        'apply',
                        'reject',
                        'set-status',
                        'get-list',
                        'set-hours',
                        'my'
                    ]],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['set-hours'],
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
            'except' => ['options', 'images', 'status', 'index', 'view'],
        ];

        // Настройка контроля доступа
        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'rules' => [
                ['allow' => true, 'roles' => ['?'], 'actions' => ['images', 'status', 'view']],
                ['allow' => true, 'roles' => ['?'], 'actions' => [
                    'index',
                    'view',
                    'update',
                    'create',
                    'delete',
                    'my',
                    'close',
                    'detail',
                    'by-coworker',
                    'free',
                    'apply',
                    'reject',
                    'set-status',
                    'get-list',
                    'set-hours',
                    'my'
                ]],
            ],
        ];

        return $behaviors;

    }

    protected function verbs()
    {
        return [
            'apply' => ['GET', 'POST', 'OPTIONS'],
            'detail' => ['GET', 'OPTIONS'],
            'view' => ['GET', 'OPTIONS'],
            'status' => ['GET', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['POST', 'PUT', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
            'check' => ['POST', 'OPTIONS'],
            'login' => ['POST', 'OPTIONS'],
            'get-list' => ['GET', 'OPTIONS'],
            'set-hours' => ['POST', 'PUT', 'OPTIONS'],
            'by-coworker' => ['GET', 'POST', 'OPTIONS'],
            'my' => ['GET', 'POST', 'OPTIONS']
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
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['options'] = [
            'class' => \yii\rest\OptionAction::class
        ];
        return $actions;
    }

    public function prepareDataProvider()
    {
//        sleep(3);
        return new \yii\data\ActiveDataProvider([
            'query' => \app\models\Order::find()->where(['created_by' => \Yii::$app->user->identity->getId()])->orderBy(['id' => SORT_DESC]),
        ]);
    }

    public function actionMy($user_id, $date = null)
    {
        $user = \app\models\User::findOne($user_id);
        if ($date) {
            $orders = $user->getOrders();
            $hour = \app\models\Hours::find()->where(['date' => $date])->andWhere(['user_id' => $user_id])->all();

            \Yii::error(count($hour));

            return $orders->andWhere(['not', ['id' => \yii\helpers\ArrayHelper::getColumn($hour, 'order_id')]])->all();
        }
        return $user->getOrders()->orderBy(['id' => SORT_DESC])->all();
    }

    public function actionByCoworker()
    {
        $coworker = \app\models\User::findOne(['id' => \Yii::$app->user->getId()]);
//        $orderCoworkers = \app\models\OrderUser::findAll(['user_id' => $coworker->id]);
//        $result = [];
//        foreach ($orderCoworkers as $oc) {
//            $result[] = $oc->order;
//        }
//        return ['data' => $result];
        return ['data' => $coworker->getSuitableOrders()->orderBy(['id' => SORT_DESC])->all()];
    }

    public function actionSetHours()
    {
        $data = $this->request->post();
        $result = [];
        $model = Hours::find()->where(["user_id" => $data["user_id"]])->andWhere(["date" => $data["date"]])->one();
        if (empty($model)) {
            \Yii::error("New model");
            $hours = new Hours([
                "order_id" => $data["order_id"],
                "user_id" => $data["user_id"],
                "date" => $data["date"],
                "count" => $data["count"],
                "is_payed" => $data["is_payed"] === '1' ? 1 : 0,
            ]);
            if ($hours->save()) {
                $result = ["ok" => true];
            } else {
                \Yii::error($hours->getErrors());
            }
        } else {
            \Yii::error("update model");
            $ok = $model->load(["Hours" => [
                    "order_id" => $data["order_id"],
                    "user_id" => $data["user_id"],
                    "date" => $data["date"],
                    "count" => $data["count"],
                    "is_payed" => $data["is_payed"] === '1' ? 1 : 0,
                ]]) && $model->save();
            if (!$ok) {
                \Yii::error($model->errors);
            }
            $result = ["ok" => $ok];
        }
        return $result;
    }

    public function actionDetail($id)
    {
        if ($id) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $model = Order::findOne($id);
            if (\Yii::$app->user->isGuest) {
                return ["ok" => false, "message" => "Unknown user"];
            }
            return ["ok" => true, "data" => $model->getDetails()];
        }
        return [];
    }

    public function actionImages()
    {
        $files = $_FILES;
        $target_path = "/upload/" . basename($files['file']['name']);

        if (move_uploaded_file($files['file']['tmp_name'], \Yii::getAlias("@webroot") . $target_path)) {
            return ["ok" => true, "data" => $target_path];
        } else {
            return ["ok" => false];
        }
    }

    public function actionClose($id)
    {
        $model = Order::findOne($id);
        $model->status = Order::STATUS_COMPLETE;
        $saved = $model->save();
        if ($saved) {
            $coworkers = $model->coworkers;
            $messages = TelegramMessage::find()
                ->where(['order_id' => $id])
                ->andWhere(['NOT IN', 'chat_id', ArrayHelper::map($coworkers, 'id', 'chat_id')])
                ->all();
            foreach ($messages as $message) {
                $message->remove();
            }
        }
        return ["ok" => $saved, "message" => $saved ? "" : $model->getErrorSummary(true)];
    }

    public function actionStatus()
    {
        $order = new Order();
        return $order->getStatusList();
    }

    public function actionFree()
    {
        $orders = Order::find()->where(['status' => Order::STATUS_NEW])->all();
        return ['data' => $orders];
    }

    public function actionApply($id)
    {
        $coworker = \app\models\User::find()
            ->where(['id' => \Yii::$app->user->getId()])
            ->one();
        $model = \app\models\Order::findOne($id);

        if (!$model->isFull() && !in_array( \Yii::$app->user->getId(), \yii\helpers\ArrayHelper::map($model->coworkers, 'id', 'id') )) {
            $model->link('coworkers', $coworker);
            if ($model->isFull()) {
                $model->status = \app\models\Order::STATUS_PROCESS;
                $model->save();
            }
            $messages = \app\models\telegram\TelegramMessage::find()->where(['order_id' => $model->id])->all();
            foreach ($messages as $message) {
                $header = $message->status ?
                    \Yii::t('app', 'You have agreed to complete the order') . " #{$model->id}" :
                    \Yii::t('app', 'New order') . " #{$model->id}";
                $message->editText(
                    null,
                    \app\components\Helper::generateTelegramMessage($model->id)
                );
            }
            return ['ok' => true];
        }
        return ['ok' => false, 'message' => \Yii::t('app', 'Order is successfully')];
    }

    public function actionReject($id)
    {
        $order = \app\models\Order::findOne($id);
        $coworker = \app\models\User::findOne(['id' => \Yii::$app->user->getId()]);
        if (isset($order) && in_array( \Yii::$app->user->getId(), \yii\helpers\ArrayHelper::map($order->coworkers, 'id', 'id') )) {
            $order->unlink('coworkers', $coworker, true);
            $order->status = \app\models\Order::STATUS_NEW;
            $order->save();
            return ['ok' => true];
        }
        return ['ok' => false, 'message' => \Yii::t('app', 'Missing order')];
    }

    public function actionSetStatus($id, $status)
    {
        $model = Order::findOne($id);
        $model->status = $status;
        $model->save();
    }

    public function actionGetList($date, $user_id)
    {
        $hours = \app\models\Hours::find()->where(['user_id' => $user_id])->andWhere(['date' => $date])->all();
        $links = \yii\helpers\ArrayHelper::map($hours, 'id', 'order_id');
        return $links;
    }
}
<?php

namespace app\modules\api\controllers;

use app\models\Coworker;
use app\models\Hours;
use app\models\Order;
use app\models\telegram\TelegramMessage;
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
                    ['allow' => true, 'roles' => ['@'], 'actions' => ['images', 'status', 'by-coworker',]],
                    ['allow' => true, 'roles' => ['?'], 'actions' => [
                        'index',
                        'view',
                        'update',
                        'create',
                        'delete',
                        'close',
                        'detail',
                        'by-coworker',
                        'free',
                        'apply',
                        'reject',
                        'set-status',
                        'get-list',
                        'set-hours'
                    ]],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['set-hours'],
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
            'get-list' => ['GET', 'OPTIONS'],
            'set-hours' => ['POST', 'PUT', 'OPTIONS'],
            'by-coworker' => ['GET', 'POST', 'OPTIONS'],
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

    public function actionByCoworker($id)
    {
//        $coworker = \app\models\User::findOne(['id' => $id]);
//        $orderCoworkers = \app\models\OrderUser::findAll(['user_id' => $coworker->id]);
//        $result = [];
//        foreach ($orderCoworkers as $oc) {
//            $result[] = $oc->order;
//        }
//        return ['data' => $result];
//        return ['data' => $coworker->orders];
    }

    public function actionSetHours()
    {
        $data = $this->request->post();
        $result = [];
        $model = Hours::find()->where(["user_id" => $data["user_id"]])->andWhere(["date" => $data["date"]])->one();
        if (empty($model)) {
            $hours = new Hours([
                "order_id" => $data["order_id"],
                "user_id" => $data["user_id"],
                "date" => $data["date"],
                "count" => $data["count"],
                "is_payed" => $data["is_payed"],
            ]);
            $result[] = ["ok" => $hours->save()];
        } else {
            $ok = $model->load(["Hours" => [
                    "order_id" => $data["order_id"],
                    "user_id" => $data["user_id"],
                    "date" => $data["date"],
                    "count" => $data["count"],
                    "is_payed" => $data["is_payed"],
                ]]) && $model->save();
            if (!$ok) {
                \Yii::error($model->errors);
            }
            $result[] = ["ok" => $ok];
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

        if (!$model->isFull()) {
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
        if (isset($order)) {
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
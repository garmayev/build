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
                    [ 'allow' => true, 'roles' => ['@'], 'actions' => ['images', 'status'] ],
                    // Users
                    [ 'allow' => true, 'roles' => ['?'], 'actions' => ['index', 'view', 'update', 'create', 'delete', 'set-hours', 'close', 'detail', 'by-coworker'] ],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['OPTIONS', 'PREFLIGHT', 'HEAD', 'images', 'status']
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
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['options'] = [
            'class' => \yii\rest\OptionAction::class
        ];
        return $actions;
    }

    public function prepareDataProvider()
    {
        return new \yii\data\ActiveDataProvider([
            'query' => $this->modelClass::find()->where(['created_by' => \Yii::$app->user->identity->getId()]),
        ]);
    }

    public function actionByCoworker()
    {
        $orderCoworker = \app\models\OrderCoworker::findAll(['coworker_id' => \Yii::$app->user->getId()]);
        return ['data' => \yii\helpers\ArrayHelper::map($orderCoworker, 'order', 'order')];
    }

    public function actionSetHours()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = [];
        foreach ($data as $key => $value) {
            $model = Hours::find()->where(["coworker_id" => $value["coworker_id"]])->andWhere(["order_id" => $value["order_id"]])->andWhere(["date" => $value["date"]])->one();
            if (empty($model)) {
                $hours = new Hours($value);
                $result[] = ["ok" => $hours->save()];
            } else {
                $result[] = ["ok" => true];
            }
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
//            \Yii::error("Something went wrong");
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
}
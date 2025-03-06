<?php

namespace app\modules\api\controllers;

use app\models\Coworker;
use app\models\Hours;
use app\models\Order;
use app\models\search\OrderSearch;
use app\models\telegram\TelegramMessage;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\rest\ActiveController;
use yii\web\UnauthorizedHttpException;
use yii\web\UploadedFile;

class OrderController extends ActiveController
{
    public $modelClass = Order::class;
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'data',
    ];

    public function behaviors()
    {
        return \yii\helpers\ArrayHelper::merge(
            parent::behaviors(),
            [
                'corsFilter' => [
                    'class' => \yii\filters\Cors::class,
                    'cors' => [
                        'Origin' => ['*'],
                        'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS', 'PREFLIGHT'],
                        'Access-Control-Request-Headers' => ['*'],
                        'Access-Control-Allow-Credentials' => false,
                        'Access-Control-Max-Age' => 86400,
                        'Access-Control-Allow-Origin' => ['*'],
                    ],
                ],
                'authenticator' => [
                    'class' => \yii\filters\auth\HttpBearerAuth::class,
                    'except' => ['detail', 'images', 'options', 'preflight', 'index', 'view']
                ],
                'contentNegotiator' => [
                    'class' => \yii\filters\ContentNegotiator::class,
                    'formats' => [
                        'application/json' => \yii\web\Response::FORMAT_JSON,
                    ]
                ],
                'access' => [
                    'class' => \yii\filters\AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index', 'view', 'create', 'update', 'delete', 'detail', 'status']
                        ]
                    ],
                ]
            ]
        );
    }

    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'OPTIONS', 'PREFLIGHT'],
            'view' => ['GET', 'HEAD', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'PATCH', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['dataFilter'] = [
            'class' => \yii\data\ActiveDataFilter::class,
            'searchModel' => $this->modelClass,
        ];
//        unset($actions['index']);
//        unset($actions['view']);
        unset($actions['create']);
        return $actions;
    }

    public function actionView($id)
    {
        if (\Yii::$app->user->isGuest) {
            return ["ok" => false, "message" => "Unknown user"];
        }
        if (\Yii::$app->user->identity->id === 1) {
            return ["ok" => true, "data" => Order::find()->all()];
        }
        return ["ok" => true, "data" => Order::findOne($id)];
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
            \Yii::error("Something went wrong");
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

    public function actionDelete($id)
    {
        $model = Order::findOne($id);
        return ["ok" => $model->delete()];
    }

    public function actionCreate()
    {
        $model = new Order();
        if (\Yii::$app->request->isPost) {
            \Yii::error( \Yii::$app->request->post() );
            if ($model->load(["Order" => \Yii::$app->request->post()]) && $model->save()) {
                $model->notify();
                return ["ok" => true, "data" => $model->getDetails()];
            } else {
                return ["ok" => false, "message" => $model->getErrorSummary(true)];
            }
        }
        return ["ok" => false, "message" => "Method not allowed"];
    }

    public function actionStatus()
    {
        $order = new Order();
        return $order->getStatusList();
    }
}
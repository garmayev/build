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
                    [ 'allow' => true, 'roles' => ['@'], 'actions' => ['images', 'status', 'set-hours'] ],
                    [ 'allow' => true, 'roles' => ['?'], 'actions' => ['index', 'view', 'update', 'create', 'delete', 'set-hours', 'close', 'detail', 'by-coworker', 'free', 'apply', 'reject', 'set-hours'] ],
                ],
            ],
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['OPTIONS', 'PREFLIGHT', 'HEAD', 'images', 'status', 'set-hours']
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
            'query' => $this->modelClass::find()->where(['created_by' => \Yii::$app->user->identity->getId()])->orderBy(['id' => SORT_ASC]),
        ]);
    }

    public function actionByCoworker()
    {
//        \Yii::error( \Yii::$app->user->isGuest );
        $coworker = \app\models\Coworker::findOne(['user_id' => \Yii::$app->user->getId()]);
        $orderCoworkers = \app\models\OrderCoworker::findAll(['coworker_id' => $coworker->id]);
        $result = [];
        foreach ($orderCoworkers as $oc) {
            $result[] = $oc->order;
        }
        return ['data' => $result];
    }

    public function actionSetHours()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = [];
//        foreach ($data as $key => $value) {
            $model = Hours::find()->where(["coworker_id" => $data["coworker_id"]])->andWhere(["date" => $data["date"]])->one();
            if (empty($model)) {
                $hours = new Hours($data);
                $result[] = ["ok" => $hours->save()];
            } else {
                $result[] = ["ok" => $model->load(["Hours" => $data]) && $model->save()];
            }
//        }
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

    public function actionFree()
    {
        $orders = Order::find()->where(['status' => Order::STATUS_NEW])->all();
        return ['data' => $orders];
    }

    public function actionApply($id)
    {
        $coworker = \app\models\Coworker::find()
            ->where(['user_id' => \Yii::$app->user->getId()])
            ->one();
        $model = \app\models\Order::findOne($id);

        if (!$model->checkSuccessfully()) {
            $model->link('coworkers', $coworker);
            $messages = \app\models\telegram\TelegramMessage::find()->where(['order_id' => $model->id])->all();
            foreach ($messages as $message) {
                $header = $message->status ? 
                    \Yii::t('app', 'You have agreed to complete the order') . " #{$model->id}" :
                    \Yii::t('app', 'New order') . " #{$model->id}";
                $message->editText(
                    null,
                    $model->generateTelegramText($header)
                );
            }
            return ['ok' => true];
        }
        return ['ok' => false, 'message' => \Yii::t('app', 'Order is successfully')];
    }

    public function actionReject($id)
    {
        $order = \app\models\Order::findOne($id);
        $coworker = \app\models\Coworker::findOne(['user_id' => \Yii::$app->user->getId()]);
        if (isset($order)) {
            $order->unlink('coworkers', $coworker, true);
            return ['ok' => true];
        }
        return ['ok' => false, 'message' => \Yii::t('app', 'Missing order')];
    }

}
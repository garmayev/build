<?php

namespace app\modules\api\controllers;

use app\models\Coworker;
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
/*            'corsFilter' => [
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
                    [ 'allow' => true, 'roles' => ['?'], 'actions' => [] ],
                    // Users
                    [ 'allow' => true, 'roles' => ['@'], 'actions' => ['check', 'list', 'view', 'create', 'suitableOrders'] ],
                ],
            ], */
            'authenticator' => [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'except' => ['OPTIONS', 'PREFLIGHT', 'HEAD']
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

<<<<<<< HEAD
    public function actionSuitableOrders()
    {
        $coworker = Coworker::findOne(['user_id' => \Yii::$app->user->getId()]);
=======
    public function actionSetToken()
    {
        $data = \Yii::$app->request->post();
        $model = Coworker::find()->where(['user_id' => $data['coworker_id']])->one();
        if ($model) {
            $model->device_id = $data['token'];
            return ['ok' => $model->save(), 'message' => $model->getErrors()];
        }
        return ['ok' => false, 'message' => \Yii::t('app', 'Unknown coworker')];
//        \Yii::error($data);
    }

    public function actionSuitableOrders()
    {
        $coworker = Coworker::findOne(['user_id' => \Yii::$app->user->getId()]);

>>>>>>> 321c2b3 (Fix)
        if (!$coworker) {
            return ["ok" => false, "message" => "Coworker not found"];
        }

        // Get all orders that match the coworker's category
        $orders = \app\models\Order::find()
<<<<<<< HEAD
//            ->where(['category_id' => $coworker->category_id])
//           ->andWhere(['not in', 'id', \app\models\OrderCoworker::find()->select('order_id')->where(['coworker_id' => $coworker->id])])
            ->all();

        $suitableOrders = [];
        foreach ($orders as $order) {
            $isSuitable = true;
            
            // Check if order has requirements
            if ($order->filter && $order->filter->requirements) {
                foreach ($order->filter->requirements as $requirement) {
                    $coworkerProperty = \app\models\CoworkerProperty::findOne([
                        'coworker_id' => $coworker->id,
                        'property_id' => $requirement->property_id
                    ]);

                    if (!$coworkerProperty) {
                        $isSuitable = false;
                        break;
                    }

                    switch ($requirement->type) {
                        case \Yii::t('app', 'Less'):
                            if ($coworkerProperty->value > $requirement->value) {
                                $isSuitable = false;
                            }
                            break;
                        case \Yii::t('app', 'More'):
                            if ($coworkerProperty->value < $requirement->value) {
                                $isSuitable = false;
                            }
                            break;
                        case \Yii::t('app', 'Equal'):
                            if ($coworkerProperty->value != $requirement->value) {
                                $isSuitable = false;
                            }
                            break;
                        case \Yii::t('app', 'Not Equal'):
                            if ($coworkerProperty->value == $requirement->value) {
                                $isSuitable = false;
                            }
                            break;
                    }

                    if (!$isSuitable) {
                        break;
                    }
                }
            }

            if ($isSuitable) {
=======
            ->where(['status' => \app\models\Order::STATUS_NEW])
            ->orderBy(['id' => SORT_DESC])
            ->all();
        $suitableOrders = [];
        $coworkerList = [];

        foreach ($orders as $order) {
            $isSuitable = true;
            foreach ($order->filters as $filter) {
                $coworkerList = array_merge($coworkerList, \app\models\Coworker::searchByFilter($filter, $order->priority_level));
            }
//            \Yii::error($coworkerList);
            if (count($coworkerList) && !$order->checkSuccessfully()) {
>>>>>>> 321c2b3 (Fix)
                $suitableOrders[] = $order;
            }
        }

        return ["ok" => true, "data" => $suitableOrders];
    }
}
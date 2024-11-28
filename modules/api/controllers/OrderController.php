<?php

namespace app\modules\api\controllers;

use app\models\Order;
use yii\rest\Controller;

class OrderController extends Controller
{
    public function beforeAction($action)
    {
        $this->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionDetail($id)
    {
        if ($id) {
            $model = Order::findOne($id);

            return $model->filters;
        }
        return [];
    }


}
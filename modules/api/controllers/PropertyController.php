<?php

namespace app\modules\api\controllers;

use app\models\Category;
use app\models\Property;
use yii\rest\Controller;

class PropertyController extends Controller
{
    public function beforeAction($action)
    {
        $this->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionIndex($type = 1)
    {
        return Property::find()->where(['type' => $type])->all();
    }

    public function actionByCategory($id)
    {
        return Category::findOne($id)->properties;
    }
}
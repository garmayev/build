<?php

namespace app\modules\api\controllers;

use app\models\Category;
use app\models\Property;
use yii\helpers\ArrayHelper;
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
        return Property::find()->all();
    }

    public function actionByCategory($id = null)
    {
        \Yii::error($id);
        if (\Yii::$app->request->isPost && isset($_POST['depdrop_all_params'])) {
            $category_id = $_POST['depdrop_all_params']['category_id'];
            $category = Category::findOne($category_id);
            $result = [];
            foreach ($category->properties as $key => $property) {
                $result[] = ["id" => $property->id, "name" => $property->title];
            }
            return ['output' => $result, 'selected' => ''];
        }
        $category = Category::findOne($id);
        return ['ok' => true, 'results' => $category->properties];
    }
}

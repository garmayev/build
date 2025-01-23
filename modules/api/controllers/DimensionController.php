<?php

namespace app\modules\api\controllers;

use app\models\Category;
use app\models\Property;
use yii\rest\Controller;

class DimensionController extends Controller
{
    public function beforeAction($action)
    {
        $this->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionByProperty()
    {
        if ( \Yii::$app->request->isPost && isset($_POST['depdrop_all_params']) ) {
            $id = $_POST['depdrop_all_params']['property_id'];
            $property = Property::findOne($id);
            $result = [];
            if ($property) {
                foreach ($property->dimensions as $key => $dimension) {
                    $result[] = ["id" => $dimension->id, "name" => $dimension->title];
                }
                return ['output' => $result, 'selected' => ''];
            }
        }
        return ['output' => '', 'selected' => ''];
    }
}

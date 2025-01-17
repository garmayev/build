<?php

namespace app\modules\api\controllers;

use app\models\Category;
use yii\rest\Controller;

class CategoryController extends Controller
{
    public function actionIndex($type = 1)
    {
        return ['results' => Category::find()->where(['type' => $type])->all()];
    }

    public function actionView($id)
    {
        return Category::findOne($id);
    }
}

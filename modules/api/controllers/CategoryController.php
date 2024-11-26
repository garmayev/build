<?php

namespace app\modules\api\controllers;

use app\models\Category;
use yii\rest\Controller;

class CategoryController extends Controller
{
    public function actionIndex($type = 1)
    {
        return Category::find()->where(['type' => 1])->all();
    }

    public function actionView($id)
    {
        return Category::findOne($id);
    }
}
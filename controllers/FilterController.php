<?php

namespace app\controllers;

use app\models\Filter;
use yii\web\Controller;

class FilterController extends Controller
{
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => Filter::findOne($id)
        ]);
    }
}
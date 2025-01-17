<?php

namespace app\controllers;

use app\models\forms\UserRegisterForm;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;

class UserController extends Controller
{
    public function actionValidateRegister()
    {
        $model = new UserRegisterForm();

        if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
    }
}

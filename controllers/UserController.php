<?php

namespace app\controllers;

use app\models\forms\LoginForm;
use app\models\forms\UserRegisterForm;
use app\models\search\UserSearch;
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

    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionLogin()
    {
        $this->layout = 'blank';
        $model = new LoginForm();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->login()) {
                return $this->goHome();
            } else {
                \Yii::error($model->errors);
            }
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }
}

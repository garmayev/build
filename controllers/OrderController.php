<?php

namespace app\controllers;

use app\models\Order;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class OrderController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => Order::find()
            ])
        ]);
    }

    public function actionView($id)
    {
        $model = Order::findOne($id);

        return $this->render('view', [
            'model' => $model
        ]);
    }

    public function actionDelete($id)
    {
        $model = Order::findOne($id);
        $model->delete();
        return $this->redirect(['/order/index']);
    }

    public function actionCoworker($id = null)
    {
        if ($id) {
            $model = Order::findOne($id);
        } else {
            $model = new Order();
        }

        if (\Yii::$app->request->isPost) {
            if ($model->load(\Yii::$app->request->post()) && $model->save()) {
                \Yii::$app->session->setFlash('success', \Yii::t('app', 'Order is successfully saved'));
                return $this->redirect('index');
            }
        }

        return $this->render('coworker', [
            'model' => $model
        ]);
    }

    public function actionMaterial()
    {
        $model = new Order();
        return $this->render('material', [
            'model' => $model
        ]);
    }
}
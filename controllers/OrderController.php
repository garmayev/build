<?php

namespace app\controllers;

use app\models\Order;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\UploadedFile;

class OrderController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'delete', 'coworker', 'material', 'get-list'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'delete', 'coworker', 'material', 'get-list'],
                        'roles' => ['@'],
                    ],
/*                    [
                        'allow' => true,
                        'actions' => ['get-list'],
                        'roles' => ['?']
                    ] */
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => Order::find()->where(['created_by' => \Yii::$app->user->identity->getId()])
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
            $model->files = UploadedFile::getInstances($model, 'files');
            if ($model->load(\Yii::$app->request->post()) && $model->save()) {
                $model->notify();
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

    public function actionGetList()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return Order::find()->where(['created_by' => \Yii::$app->user->getId()])->andWhere(['<>', 'status', Order::STATUS_COMPLETE])->all();
    }
}

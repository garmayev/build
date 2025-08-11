<?php

namespace app\controllers;

use app\models\Config;
use app\models\search\ConfigSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;

class ConfigController extends BaseController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['update', 'interval'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['update', 'interval'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    public function actionInterval()
    {
        $searchModel = new ConfigSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('interval', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = Config::findOne($id);
        if (\Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                \Yii::$app->session->setFlash('success', \Yii::t('app', 'Config is updated'));
                return $this->redirect(['interval']);
            }
        }
        return $this->render('update', [
            'model' => $model
        ]);
    }
}
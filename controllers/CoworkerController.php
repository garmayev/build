<?php

namespace app\controllers;

use app\models\Coworker;
use app\models\Profile;
use app\models\search\CoworkerSearch;
use app\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CoworkerController implements the CRUD actions for Coworker model.
 */
class CoworkerController extends Controller
{
    /**
     * Lists all Coworker models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CoworkerSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Coworker model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Coworker model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Coworker();

        if ($this->request->isPost) {
            $data = $this->request->post();
            if ($data['Coworker']['user']) {
                $profile = new Profile();
                $profile->load(['Profile' => $data['Coworker']['user']['profile']]) && $profile->save();
                $user = new User();
                if (isset($data['Coworker']['user']['password'])) {
                    $data['Coworker']['user']['password_hash'] = \Yii::$app->security->generatePasswordHash($data['Coworker']['user']['password']);
                    $data['Coworker']['user']['auth_key'] = \Yii::$app->security->generateRandomString(16);
                    $data['Coworker']['user']['access_token'] = \Yii::$app->security->generateRandomString(16);
                } else {
                    $data['Coworker']['user']['password_hash'] = \Yii::$app->security->generatePasswordHash( \Yii::$app->security->generateRandomString(16) );
                    $data['Coworker']['user']['auth_key'] = \Yii::$app->security->generateRandomString(16);
                    $data['Coworker']['user']['access_token'] = \Yii::$app->security->generateRandomString(16);
                }
                $user->load(["User" => $data['Coworker']['user']]) && $user->save();
                $user->link('profile', $profile);
                if ($model->load($this->request->post()) && $model->save()) {
                    $model->link('user', $user);
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Coworker model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = $this->request->post();

        if ($this->request->isPost) {

            if (isset($post['Coworker']['user_id']))
            {
                \Yii::error($post['Coworker']['user']);
                $profile = Profile::findOne($post['Coworker']['user_id']);
                $profile->load(["Profile" => $post['Coworker']['user']['profile']]) && $profile->save();
//                $user = User::findOne($post['Coworker']['user_id']);
//                $user->load(["User" => $post['Coworker']['user']]) && $profile->save();
                $model->load($post) && $model->save();
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Coworker model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Coworker model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Coworker the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Coworker::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}

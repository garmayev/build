<?php

namespace app\controllers;

use app\models\Coworker;
use app\models\forms\UserRegisterForm;
use app\models\Profile;
use app\models\search\CoworkerSearch;
use app\models\search\UserSearch;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\caching\TagDependency;

/**
 * CoworkerController implements the CRUD actions for Coworker model.
 */
class CoworkerController extends Controller
{
    public $modelClass = Coworker::class;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
                        'roles' => ['director'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'create' => ['GET', 'POST'],
                    'update' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Coworker models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
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
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
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
        $userForm = new UserRegisterForm();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($this->request->isPost) {
                $model->user_id = Yii::$app->user->identity->id;
                $model->files = UploadedFile::getInstances($model, 'files');
                $data = \Yii::$app->request->post();
                if ($model->load($data) && $model->validate()) {
                    if ($model->upload() && $model->save()) {
                        if ($data['Coworker']['coworkerProperties']) {
                            $model->setCoworkerProperties($data["Coworker"]["coworkerProperties"]);
                        }
                        $transaction->commit();
                        \Yii::$app->session->setFlash('success', \Yii::t('app', 'Coworker was successfully created'));
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
                $transaction->rollBack();
                \Yii::$app->session->setFlash('danger', \Yii::t('app', 'Coworker is not created'));
                \Yii::error('Failed to create coworker: ' . json_encode($model->getErrorSummary(true)));
            } else {
                $model->loadDefaultValues();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Exception while creating coworker: ' . $e->getMessage());
            throw $e;
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
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($this->request->isPost) {
                $model->files = UploadedFile::getInstances($model, 'files');
                if ($model->load($this->request->post()) && $model->validate()) {
                    if ($model->upload() && $model->save()) {
                        $transaction->commit();
                        \Yii::$app->session->setFlash('success', \Yii::t('app', 'Coworker was successfully updated'));
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
                $transaction->rollBack();
                \Yii::$app->session->setFlash('danger', \Yii::t('app', 'Coworker is not updated'));
                Yii::error('Failed to update coworker: ' . json_encode($model->getErrorSummary(true)));
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Exception while updating coworker: ' . $e->getMessage());
            throw $e;
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
        $model = $this->findModel($id);
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($model->delete()) {
                TagDependency::invalidate(Yii::$app->cache, ['coworker-' . $id, 'coworker-list']);
                $transaction->commit();
            } else {
                $transaction->rollBack();
                Yii::error('Failed to delete coworker: ' . json_encode($model->getErrorSummary(true)));
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Exception while deleting coworker: ' . $e->getMessage());
            throw $e;
        }

        return $this->redirect(['index']);
    }

    public function actionAccount($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model
        ]);
    }

    public function actionProfile($id)
    {
        $model = $this->findModel($id);
        return $this->render('profile', [
            'model' => $model
        ]);
    }

    public function actionInvite($id)
    {
        $model = $this->findModel($id);
        $model->invite();
        return $this->redirect(['index']);
    }

    public function actionTelegramLink($id)
    {
        $botName = \Yii::$app->params["bot_name"];
        $link = "https://t.me/{$botName}?start={$id}";
        \Yii::$app->session->setFlash('success', \yii\helpers\Html::a($link, $link, ['class' => 'text-primary']));
        return $this->redirect(\Yii::$app->request->referrer);
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
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}

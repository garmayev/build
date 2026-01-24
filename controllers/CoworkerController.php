<?php

namespace app\controllers;

use app\models\Coworker;
use app\models\forms\UserRegisterForm;
use app\models\Order;
use app\models\Profile;
use app\models\search\CoworkerSearch;
use app\models\search\UserSearch;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
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
                'only' => ['index', 'view', 'create', 'update', 'delete', 'calendar', 'list'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'calendar'],
                        'roles' => ['@'],
                    ], [
                        'allow' => true,
                        'actions' => ['list'],
                        'roles' => ['?', '@'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'create' => ['GET', 'POST'],
                    'update' => ['GET', 'POST'],
                    'calendar' => ['GET', 'POST'],
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
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Форма добавления свойства
     */
    public function actionAddProperty()
    {
        $model = new \app\models\UserProperty();
        $model->user_id = Yii::$app->request->get('user_id', null);

        // Если переданы данные через GET (для редактирования)
        if (Yii::$app->request->get('property_id')) {
            $model->property_id = Yii::$app->request->get('property_id');
        }
        if (Yii::$app->request->get('dimension_id')) {
            $model->dimension_id = Yii::$app->request->get('dimension_id');
        }
        if (Yii::$app->request->get('value')) {
            $model->value = Yii::$app->request->get('value');
        }

        return $this->renderAjax('_add_property', [
            'model' => $model,
        ]);
    }

    public function actionGetProperties()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $categoryId = Yii::$app->request->post('depdrop_parents')[0] ?? null;

        $category = \app\models\Category::findOne($categoryId);
        $properties = $category->properties;

        $output = [];
        foreach ($properties as $property) {
            $output[] = [
                'id' => $property->id,
                'name' => $property->title
            ];
        }

        return ['output' => $output];
    }
    public function actionGetDimensions()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $property_id = Yii::$app->request->post('depdrop_parents')[1] ?? null;

        $property = \app\models\Property::findOne($property_id);
        if (empty($property)) return ['output' => []];
        $dimensions = $property->dimensions;

        $output = [];
        foreach ($dimensions as $dimension) {
            $output[] = [
                'id' => $dimension->id,
                'name' => $dimension->title
            ];
        }

        return ['output' => $output];
    }

    /**
     * Creates a new Coworker model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Coworker();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($this->request->isPost) {
                $model->referrer_id = Yii::$app->user->identity->id;
//                $model->files = UploadedFile::getInstances($model, 'files');
                $data = \Yii::$app->request->post();
                if ($model->load($data) && $model->save()) {
                    $transaction->commit();
                    \Yii::$app->session->setFlash('success', \Yii::t('app', 'Coworker was successfully created'));
                    return $this->redirect(['view', 'id' => $model->id]);
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
        $model->unlinkAll('profile', true);
        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * Создание/редактирование сотрудника
     */
    public function actionAccount($id = null)
    {
        $registerForm = new \app\models\forms\UserRegisterForm();

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();

            // Загрузка данных формы
            if ($registerForm->load($postData) && $registerForm->validate()) {
                $registerForm->register($id);
                $this->redirect(['index']);
            } else {
                \Yii::error($registerForm->getErrors());
                Yii::$app->session->setFlash('error', Yii::t('app', 'Please fix the errors below'));
            }
        }

        return $this->render('account', [
            'registerForm' => $registerForm,
        ]);
    }

    /**
     * Отправка приветственного письма с данными для входа
     */
    private function sendWelcomeEmail($user, $password)
    {
        try {
            Yii::$app->mailer
                ->compose('coworker/welcome', ['user' => $user, 'password' => $password])
                ->setFrom([Yii::$app->params['adminEmail'] ?? 'noreply@example.com' => Yii::$app->name])
                ->setTo($user->email)
                ->setSubject(Yii::t('app', 'Welcome to {appName}', ['appName' => Yii::$app->name]))
                ->send();
        } catch (\Exception $e) {
            Yii::error('Ошибка отправки письма: ' . $e->getMessage());
        }
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

    public function actionList()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if (\Yii::$app->user->isGuest) {
            return [];
        } else {
//            return Order::find()->joinWith(['coworkers'])->where(['coworker.referrer_id' => \Yii::$app->user->identity->id])->all();
            return Coworker::find()->where(['referrer_id' => \Yii::$app->user->id])->all();
        }
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

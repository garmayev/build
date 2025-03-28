<?php

namespace app\controllers;

use app\models\ContactForm;
use app\models\forms\LoginForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['login', 'logout', 'index'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout', 'index'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
//        \Yii::error(\Yii::$app->telegram);
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $this->layout = 'blank';

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionCalendar()
    {
        return $this->render('calendar');
    }

    public function actionCalendarMonth($year, $month)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $result = [];
        $coworkers = \app\models\Coworker::find()->where(['created_by' => \Yii::$app->user->getId()])->orWhere(['priority' => \app\models\Coworker::PRIORITY_LOW])->all();
        foreach ($coworkers as $coworker) {
            $hours = \app\models\Hours::find()
                ->where(['>', 'date', date("$year-$month-01")])
                ->andWhere(['<', 'date', date("$year-$month-".cal_days_in_month(CAL_GREGORIAN, $month, $year))])
                ->andWhere(['coworker_id' => $coworker->id])
                ->all();
            $result[] = [
                'name' => $coworker->firstname.' '.$coworker->lastname,
                'data' => $hours,
                'total' => 0
            ];
//          \Yii::error( count($coworkers) );
        }
        \Yii::error($result);
        return $result;
    }
}

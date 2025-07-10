<?php

namespace app\controllers;

use yii\filters\AccessControl;

class SiteController extends BaseController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
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
                        'actions' => ['logout', 'index', 'calendar-month'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
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

    public function actionCalendar()
    {
        return $this->render('calendar');
    }

    public function actionCalendarMonth($year, $month)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $result = [];
        \Yii::error( \Yii::$app->user->getId() );
        $coworkers = \app\models\Coworker::find()->where(['created_by' => \Yii::$app->user->getId()])->orWhere(['priority' => \app\models\Coworker::PRIORITY_LOW])->all();
        foreach ($coworkers as $coworker) {
            $hours = \app\models\Hours::find()
                ->where(['>=', 'date', date("$year-$month-01")])
                ->andWhere(['<=', 'date', date("$year-$month-".cal_days_in_month(CAL_GREGORIAN, $month, $year))])
                ->andWhere(['coworker_id' => $coworker->id])
                ->all();
            $result[] = [
                'id' => $coworker->id,
                'name' => $coworker->firstname.' '.$coworker->lastname,
                'data' => $hours,
                'total' => 0
            ];
        }
        return $result;
    }
}

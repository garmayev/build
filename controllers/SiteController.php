<?php

namespace app\controllers;

use app\models\Coworker;
use app\models\Hours;
use app\models\Order;
use yii\filters\AccessControl;

class SiteController extends BaseController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['login', 'logout', 'index', 'calendar', 'builder'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout', 'index', 'calendar-month', 'calendar', 'builder', 'mark-order-paid'],
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

    public function beforeAction($action)
    {
        switch ($action->id) {
            case 'mark-hours-paid':
            case 'mark-order-paid':
                $this->enableCsrfValidation = false;
                break;
        }
        return parent::beforeAction($action);
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

    public function actionCalendar($month = null, $year = null)
    {
        if (!$month) $month = date('n');
        if (!$year) $year = date('Y');

        $employees = Coworker::find()->all();

        return $this->render('calendar', [
            'month' => $month,
            'year' => $year,
            'employees' => $employees,
            'currentDate' => date('Y-m-d'),
        ]);
    }

    public function actionDayInfo($employee_id, $date)
    {
        // Этот экшен может возвращать информацию о конкретном дне для модального окна
        $employee = Coworker::findOne($employee_id);

        return $this->renderPartial('_day_info', [
            'employee' => $employee,
            'date' => $date,
        ]);
    }

    public function actionCalendarMonth($year, $month)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $result = [];
        $coworkers = \app\models\Coworker::find()->where(['referrer_id' => \Yii::$app->user->getId()])->orWhere(['priority_level' => \app\models\Coworker::PRIORITY_LOW])->all();
        foreach ($coworkers as $coworker) {
            $hours = \app\models\Hours::find()
                ->where(['>=', 'date', date("$year-$month-01")])
                ->andWhere(['<=', 'date', date("$year-$month-".cal_days_in_month(CAL_GREGORIAN, $month, $year))])
                ->andWhere(['user_id' => $coworker->id])
                ->all();
            $result[] = [
                'user' => $coworker,
                'hours' => $coworker->hours,
                'orders' => $coworker->orders,
            ];
        }
        return $result;
    }

    public function actionBuilder()
    {
        return $this->render('builder');
    }

    public function actionMarkHoursPaid()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $query = Hours::find();
        foreach ($data as $key => $field) {
            $query->andWhere([$key => $field]);
        }
        $model = $query->one();
        $model->is_payed = true;
        return $model->save();
    }

    public function actionMarkOrderPaid()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = Order::findOne($data['order_id']);
        $model->is_payed = 1;
        if ($model->save()) {
            \Yii::error($model->attributes);
            return ['ok' => true];
        } else {
            \Yii::error($model->errors);
        }
        return ['ok' => false];
    }
}

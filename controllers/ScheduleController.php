<?php

namespace app\controllers;

use app\models\Building;
use app\models\Coworker;
use app\models\Hours;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class ScheduleController extends BaseController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Главная страница редактора расписания
     * @param int|null $month
     * @param int|null $year
     * @return string
     */
    public function actionIndex($month = null, $year = null)
    {
        if (!$month) $month = date('n');
        if (!$year) $year = date('Y');

        // Получаем всех сотрудников
        $employees = Coworker::find()->all();
        
        // Получаем все филиалы
        $buildings = Building::find()->all();

        return $this->render('index', [
            'month' => $month,
            'year' => $year,
            'employees' => $employees,
            'buildings' => $buildings,
            'currentDate' => date('Y-m-d'),
        ]);
    }

    /**
     * Получить расписание для сотрудника на дату
     * @return Response
     */
    public function actionGetSchedule()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $user_id = Yii::$app->request->get('user_id');
        $date = Yii::$app->request->get('date');
        
        if (!$user_id || !$date) {
            return ['success' => false, 'message' => 'Не указаны обязательные параметры'];
        }
        
        $schedule = Hours::find()
            ->where(['user_id' => $user_id, 'date' => $date])
            ->orderBy(['start_time' => SORT_ASC])
            ->all();
        
        return [
            'success' => true,
            'data' => $schedule
        ];
    }

    /**
     * Создать или обновить запись расписания
     * @return Response
     */
    public function actionSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $data = Yii::$app->request->post();
        
        if (isset($data['id']) && $data['id']) {
            // Обновление существующей записи
            $model = Hours::findOne($data['id']);
            if (!$model) {
                return ['success' => false, 'message' => 'Запись не найдена'];
            }
        } else {
            // Создание новой записи
            $model = new Hours();
        }
        
        // Загружаем данные
        $model->load($data, '');
        
        // Вычисляем количество часов, если указано время начала и окончания
        if ($model->start_time && $model->stop_time) {
            $start = strtotime($model->start_time);
            $stop = strtotime($model->stop_time);
            if ($start && $stop && $stop > $start) {
                $model->count = round(($stop - $start) / 3600, 2);
            }
        }
        
        // Для обедов, больничных и выходных order_id и building_id не обязательны
        if ($model->type !== Hours::TYPE_WORK) {
            $model->order_id = null;
            if ($model->type === Hours::TYPE_DAY_OFF || $model->type === Hours::TYPE_SICK) {
                $model->building_id = null;
            }
        }
        
        if ($model->save()) {
            return [
                'success' => true,
                'message' => 'Расписание сохранено',
                'data' => $model
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка сохранения',
                'errors' => $model->errors
            ];
        }
    }

    /**
     * Удалить запись расписания
     * @return Response
     */
    public function actionDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $id = Yii::$app->request->post('id');
        
        if (!$id) {
            return ['success' => false, 'message' => 'Не указан ID записи'];
        }
        
        $model = Hours::findOne($id);
        if (!$model) {
            return ['success' => false, 'message' => 'Запись не найдена'];
        }
        
        if ($model->delete()) {
            return ['success' => true, 'message' => 'Запись удалена'];
        } else {
            return ['success' => false, 'message' => 'Ошибка удаления'];
        }
    }

    /**
     * Получить расписание на месяц
     * @return Response
     */
    public function actionGetMonthSchedule()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $month = Yii::$app->request->get('month');
        $year = Yii::$app->request->get('year');
        
        if (!$month || !$year) {
            return ['success' => false, 'message' => 'Не указаны месяц и год'];
        }
        
        $startDate = date("$year-$month-01");
        $endDate = date("$year-$month-" . cal_days_in_month(CAL_GREGORIAN, $month, $year));
        
        $schedule = Hours::find()
            ->where(['>=', 'date', $startDate])
            ->andWhere(['<=', 'date', $endDate])
            ->orderBy(['date' => SORT_ASC, 'start_time' => SORT_ASC])
            ->all();
        
        // Группируем по сотрудникам и датам
        $result = [];
        foreach ($schedule as $item) {
            if (!isset($result[$item->user_id])) {
                $result[$item->user_id] = [];
            }
            if (!isset($result[$item->user_id][$item->date])) {
                $result[$item->user_id][$item->date] = [];
            }
            $result[$item->user_id][$item->date][] = $item;
        }
        
        return [
            'success' => true,
            'data' => $result
        ];
    }
}


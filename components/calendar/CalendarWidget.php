<?php
namespace app\components\calendar;

use app\models\Coworker;
use app\models\Hours;
use app\models\Order;
use app\components\calendar\assets\CalendarAsset;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Виджет календаря для отображения заказов с объединением ячеек
 */
class CalendarWidget extends Widget
{
    /**
     * @var array Массив работников (User модели)
     */
    public $employees = [];

    /**
     * @var int Год для отображения
     */
    public $year;

    /**
     * @var int Месяц для отображения (1-12)
     */
    public $month;

    /**
     * @var string|null Текущая дата (для выделения)
     */
    public $currentDate;

    /**
     * @var array Конфигурация виджета
     */
    public $config = [];

    /**
     * @var array Заказы по дням (для быстрого доступа)
     */
    private $ordersByDayAndEmployee = [];

    /**
     * @var array Часы работы по дням и сотрудникам
     */
    private $hoursByDayAndEmployee = [];

    /**
     * @var int Дней в месяце
     */
    private $daysInMonth;

    /**
     * @var array Дни недели
     */
    private $weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!$this->year) {
            $this->year = date('Y');
        }
        if (!$this->month) {
            $this->month = date('n');
        }

        $this->daysInMonth = date('t', mktime(0, 0, 0, $this->month, 1, $this->year));

        if (!$this->currentDate) {
            $this->currentDate = date('Y-m-d');
        }

        // Если не переданы работники, получаем всех
        if (empty($this->employees)) {
            $this->employees = Coworker::find()->all();
        }

        // Группируем заказы и часы по дням и сотрудникам
        $this->loadOrdersAndHours();
    }

    /**
     * Загружает заказы и часы работы
     */
    private function loadOrdersAndHours()
    {
        $startDate = mktime(0, 0, 0, $this->month, 1, $this->year);
        $endDate = mktime(23, 59, 59, $this->month, $this->daysInMonth, $this->year);

        // Загружаем заказы за месяц
        $orders = Order::find()
            ->with(['coworkers', 'hours'])
            ->where(['>=', 'date', $startDate])
            ->andWhere(['<=', 'date', $endDate])
            ->all();

        // Группируем заказы по сотрудникам и дням
        $this->groupOrdersByDayAndEmployee($orders);

        // Загружаем часы работы за месяц
        $this->loadHoursByDayAndEmployee($startDate, $endDate);
    }

    /**
     * Группирует заказы по дням и сотрудникам
     *
     * @param array $orders Массив заказов
     */
    private function groupOrdersByDayAndEmployee(array $orders)
    {
        foreach ($orders as $order) {
            $orderDay = date('j', $order->date);
            $duration = $this->getOrderDuration($order);

            // Для каждого сотрудника в заказе
            foreach ($order->coworkers as $employee) {
                $employeeId = $employee->id;

                // Добавляем заказ на все дни его длительности
                for ($i = 0; $i < $duration; $i++) {
                    $currentDay = $orderDay + $i;
                    if ($currentDay > $this->daysInMonth) {
                        break;
                    }

                    $dayKey = $currentDay;

                    if (!isset($this->ordersByDayAndEmployee[$employeeId])) {
                        $this->ordersByDayAndEmployee[$employeeId] = [];
                    }

                    if (!isset($this->ordersByDayAndEmployee[$employeeId][$dayKey])) {
                        $this->ordersByDayAndEmployee[$employeeId][$dayKey] = [];
                    }

                    $this->ordersByDayAndEmployee[$employeeId][$dayKey][] = [
                        'order' => $order,
                        'isStartDay' => ($i === 0),
                        'dayOffset' => $i,
                        'duration' => $duration
                    ];
                }
            }
        }
    }

    /**
     * Загружает часы работы по дням и сотрудникам
     *
     * @param int $startDate Начальная дата (timestamp)
     * @param int $endDate Конечная дата (timestamp)
     */
    private function loadHoursByDayAndEmployee(int $startDate, int $endDate)
    {
        $hours = Hours::find()
            ->where(['>=', 'date', date('Y-m-d', $startDate)])
            ->andWhere(['<=', 'date', date('Y-m-d', $endDate)])
            ->all();

        foreach ($hours as $hour) {
            $employeeId = $hour->user_id;
            $day = date('j', strtotime($hour->date));

            if (!isset($this->hoursByDayAndEmployee[$employeeId])) {
                $this->hoursByDayAndEmployee[$employeeId] = [];
            }

            $this->hoursByDayAndEmployee[$employeeId][$day] = $hour;
        }
    }

    /**
     * Получает длительность заказа в днях
     *
     * @param Order $order
     * @return int
     */
    private function getOrderDuration(Order $order): int
    {
        switch ($order->mode) {
            case Order::MODE_SINGLE_FIXED:
                return 1;
            case Order::MODE_LONG_FIXED:
                // Для MODE_LONG_FIXED длительность определяется из summary
                return $this->getLongFixedDuration($order);
            case Order::MODE_LONG_DAILY:
                // Для ежедневных заказов показываем каждый день отдельно
                return 1;
            default:
                return 1;
        }
    }

    /**
     * Получает длительность для MODE_LONG_FIXED заказа
     *
     * @param Order $order
     * @return int
     */
    private function getLongFixedDuration(Order $order): int
    {
        if (!empty($order->summary) && is_numeric($order->summary)) {
            $duration = intval($order->summary);
            return $duration > 0 ? $duration : 1;
        }

        return 1; // Значение по умолчанию
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this->renderCalendar();
    }

    /**
     * Рендерит календарь
     *
     * @return string
     */
    private function renderCalendar(): string
    {
        $output = '';

        // Панель управления
        $output .= $this->renderControlPanel();

        // Таблица календаря
        $output .= $this->renderCalendarTable();

        return Html::tag('div', $output, ['class' => 'horizontal-calendar-widget']);
    }

    /**
     * Рендерит панель управления календарем
     *
     * @return string
     */
    private function renderControlPanel(): string
    {
        $prevMonth = $this->month - 1;
        $prevYear = $this->year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $nextMonth = $this->month + 1;
        $nextYear = $this->year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        $monthNames = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
            5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
            9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
        ];

        $currentMonth = $monthNames[$this->month] . ' ' . $this->year;

        $controls = Html::tag('div', $currentMonth, ['class' => 'calendar-title']);

        // Кнопки навигации
        $navButtons = Html::a(
                '←',
                '#',
                [
                    'class' => 'calendar-nav-button prev-month',
                    'data-month' => $prevMonth,
                    'data-year' => $prevYear
                ]
            ) . Html::a(
                '→',
                '#',
                [
                    'class' => 'calendar-nav-button next-month',
                    'data-month' => $nextMonth,
                    'data-year' => $nextYear
                ]
            );

        $controls .= Html::tag('div', $navButtons, ['class' => 'calendar-nav']);

        return Html::tag('div', $controls, ['class' => 'calendar-controls']);
    }

    /**
     * Рендерит таблицу календаря
     *
     * @return string
     */
    private function renderCalendarTable(): string
    {
        $output = '<div class="calendar-table-container">';
        $output .= '<table class="calendar-table">';

        // Заголовок с днями месяца
        $output .= $this->renderTableHeader();

        // Строки для каждого работника
        $output .= $this->renderEmployeeRows();

        $output .= '</table>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Рендерит заголовок таблицы с днями месяца
     *
     * @return string
     */
    private function renderTableHeader(): string
    {
        $output = '<thead><tr>';

        // Пустая ячейка для имен работников
        $output .= '<th class="employee-header">Сотрудник</th>';

        // Ячейки с днями месяца
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $dayOfWeek = date('N', mktime(0, 0, 0, $this->month, $day, $this->year));
            $isWeekend = ($dayOfWeek >= 6);
            $isToday = ($this->year . '-' . sprintf('%02d', $this->month) . '-' . sprintf('%02d', $day)) === $this->currentDate;

            $classes = ['day-header'];
            if ($isWeekend) {
                $classes[] = 'weekend';
            }
            if ($isToday) {
                $classes[] = 'today';
            }

            $dayName = $this->weekDays[$dayOfWeek - 1];
            $title = $dayName . ', ' . $day;

            $output .= Html::tag('th', $title, [
                'class' => implode(' ', $classes),
                'data-day' => $day,
                'title' => $title
            ]);
        }

        $output .= '</tr></thead>';
        return $output;
    }

    /**
     * Рендерит строки для каждого работника
     *
     * @return string
     */
    private function renderEmployeeRows(): string
    {
        $output = '<tbody>';

        foreach ($this->employees as $employee) {
            $output .= $this->renderEmployeeRow($employee);
        }

        $output .= '</tbody>';
        return $output;
    }

    /**
     * Рендерит строку для одного работника
     *
     * @param Coworker $employee
     * @return string
     */
    private function renderEmployeeRow(Coworker $employee): string
    {
        $output = '<tr>';

        // Ячейка с именем работника
        $employeeName = $employee->getName();
        $output .= Html::tag('td', $employeeName, [
            'class' => 'employee-name',
            'title' => $employeeName . ' (' . $employee->email . ')',
            'data-employee-id' => $employee->id
        ]);

        // Ячейки с днями месяца
        $processedDays = []; // Для отслеживания объединенных ячеек
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            // Если день уже был частью объединенной ячейки, пропускаем
            if (isset($processedDays[$day])) {
                continue;
            }

            $output .= $this->renderDayCell($employee, $day, $processedDays);
        }

        $output .= '</tr>';
        return $output;
    }

    /**
     * Рендерит ячейку дня для работника
     *
     * @param Coworker $employee
     * @param int $day
     * @param array &$processedDays
     * @return string
     */
    private function renderDayCell(Coworker $employee, int $day, array &$processedDays): string
    {
        $employeeId = $employee->id;
        $dayOfWeek = date('N', mktime(0, 0, 0, $this->month, $day, $this->year));
        $isWeekend = ($dayOfWeek >= 6);
        $isToday = ($this->year . '-' . sprintf('%02d', $this->month) . '-' . sprintf('%02d', $day)) === $this->currentDate;

        // Проверяем, есть ли заказы на этот день
        $hasOrders = isset($this->ordersByDayAndEmployee[$employeeId][$day]);
        $hasHours = isset($this->hoursByDayAndEmployee[$employeeId][$day]);

        $content = '';
        $classes = ['day-cell'];
        $colspan = 1;
        $title = '';

        if ($isWeekend) {
            $classes[] = 'weekend';
        }
        if ($isToday) {
            $classes[] = 'today';
        }

        if ($hasOrders) {
            $ordersData = $this->ordersByDayAndEmployee[$employeeId][$day];

            // Ищем заказ, который начинается в этот день и имеет длительность > 1
            $orderData = null;
            foreach ($ordersData as $data) {
                if ($data['isStartDay'] && $data['duration'] > 1) {
                    $orderData = $data;
                    break;
                }
            }

            if ($orderData) {
                // Объединенная ячейка для длительного заказа
                $colspan = min($orderData['duration'], $this->daysInMonth - $day + 1);
                $content = $this->renderOrderContent($orderData['order'], $employee, $day, $colspan);
                $classes[] = 'merged-cell';

                // Добавляем класс в зависимости от режима заказа
                if ($orderData['order']->mode == Order::MODE_SINGLE_FIXED) {
                    $classes[] = 'single-fixed';
                } elseif ($orderData['order']->mode == Order::MODE_LONG_FIXED) {
                    $classes[] = 'long-fixed';
                }

                // Помечаем дни, которые будут объединены
                for ($i = 0; $i < $colspan; $i++) {
                    $processedDays[$day + $i] = true;
                }
            } else {
                // Обычная ячейка с заказом
                $content = $this->renderOrdersContent($ordersData, $employee, $day);
                $classes[] = 'has-order';
            }
        } elseif ($hasHours) {
            // Ячейка только с часами работы
            $hours = $this->hoursByDayAndEmployee[$employeeId][$day];
            $content = $this->renderHoursContent($hours);
            $classes[] = 'has-hours';
        } else {
            // Пустая ячейка
            $classes[] = 'empty-cell';
        }

        // Добавляем класс в зависимости от статуса
        if ($hasOrders) {
            $firstOrder = $this->ordersByDayAndEmployee[$employeeId][$day][0]['order'];
            $classes[] = 'status-' . $firstOrder->status;
        }

        $attributes = [
            'class' => implode(' ', $classes),
            'data-employee-id' => $employeeId,
            'data-day' => $day,
            'data-date' => $this->year . '-' . sprintf('%02d', $this->month) . '-' . sprintf('%02d', $day)
        ];

        if ($colspan > 1) {
            $attributes['colspan'] = $colspan;
        }

        if ($title) {
            $attributes['title'] = $title;
        }

        return Html::tag('td', $content, $attributes);
    }

    /**
     * Рендерит содержимое для объединенной ячейки с заказом
     *
     * @param Order $order
     * @param Coworker $employee
     * @param int $day
     * @param int $duration
     * @return string
     */
    private function renderOrderContent(Order $order, Coworker $employee, int $day, int $duration): string
    {
        $hours = isset($this->hoursByDayAndEmployee[$employee->id][$day])
            ? $this->hoursByDayAndEmployee[$employee->id][$day]
            : null;

        $content = Html::tag('div', 'Заказ #' . $order->id, ['class' => 'order-id']);

        if ($duration > 1) {
            $content .= Html::tag('div', $duration . ' дн.', ['class' => 'order-duration']);
        }

        if ($hours) {
            $content .= Html::tag('div',
                $hours->count . ' ч. (' .
                ($hours->start_time ? date('H:i', strtotime($hours->start_time)) : '--') . ' - ' .
                ($hours->stop_time ? date('H:i', strtotime($hours->stop_time)) : '--') . ')',
                ['class' => 'order-hours']
            );
        }

        $content .= Html::tag('div', $order->getStatusTitle(), ['class' => 'order-status']);

        return Html::tag('div', $content, [
            'class' => 'order-content merged',
            'data-order-id' => $order->id,
            'onclick' => 'window.location.href=\'' . Url::to(['order/view', 'id' => $order->id]) . '\''
        ]);
    }

    /**
     * Рендерит содержимое для ячейки с несколькими заказами
     *
     * @param array $ordersData
     * @param Coworker $employee
     * @param int $day
     * @return string
     */
    private function renderOrdersContent(array $ordersData, Coworker $employee, int $day): string
    {
        $content = '';
        $hours = isset($this->hoursByDayAndEmployee[$employee->id][$day])
            ? $this->hoursByDayAndEmployee[$employee->id][$day]
            : null;

        foreach ($ordersData as $data) {
            $order = $data['order'];
            $content .= Html::tag('div', 'Заказ #' . $order->id, [
                'class' => 'order-id',
                'data-order-id' => $order->id,
                'onclick' => 'window.location.href=\'' . Url::to(['order/view', 'id' => $order->id]) . '\''
            ]);
        }

        if ($hours) {
            $content .= Html::tag('div',
                $hours->count . ' ч. ' .
                ($hours->start_time ? date('H:i', strtotime($hours->start_time)) . '-' . date('H:i', strtotime($hours->stop_time)) : ''),
                ['class' => 'order-hours']
            );
        }

        return Html::tag('div', $content, ['class' => 'order-content']);
    }

    /**
     * Рендерит содержимое для ячейки только с часами
     *
     * @param Hours $hours
     * @return string
     */
    private function renderHoursContent(Hours $hours): string
    {
        $content = Html::tag('div', $hours->count . ' ч.', ['class' => 'hours-count']);

        if ($hours->start_time && $hours->stop_time) {
            $content .= Html::tag('div',
                date('H:i', strtotime($hours->start_time)) . ' - ' .
                date('H:i', strtotime($hours->stop_time)),
                ['class' => 'hours-time']
            );
        }

        if ($hours->order_id) {
            $content .= Html::tag('div', 'Заказ #' . $hours->order_id, [
                'class' => 'hours-order',
                'onclick' => 'window.location.href=\'' . Url::to(['order/view', 'id' => $hours->order_id]) . '\''
            ]);
        }

        $statusClass = $hours->is_payed ? 'payed' : 'un-payed';
        $content .= Html::tag('div', $hours->is_payed ? 'Оплачено' : 'Не оплачено', [
            'class' => 'hours-status ' . $statusClass
        ]);

        return Html::tag('div', $content, ['class' => 'hours-content']);
    }
}
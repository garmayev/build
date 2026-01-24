<?php
// @app/helpers/CalendarHelper.php

namespace app\components\calendar;

class CalendarHelper
{
    /**
     * Проверяет, нужно ли объединять ячейки для заказа
     *
     * @param \app\models\Order $order
     * @return bool
     */
    public static function shouldMergeCells($order)
    {
        return in_array($order->mode, [
            \app\models\Order::MODE_SINGLE_FIXED,
            \app\models\Order::MODE_LONG_FIXED
        ]);
    }

    /**
     * Получает дату окончания заказа
     *
     * @param \app\models\Order $order
     * @return int timestamp
     */
    public static function getOrderEndDate($order)
    {
        $startDate = $order->date;

        switch ($order->mode) {
            case \app\models\Order::MODE_SINGLE_FIXED:
                return $startDate + 86400; // +1 день

            case \app\models\Order::MODE_LONG_FIXED:
                // Здесь логика расчета даты окончания
                // Например, из summary или другого поля
                $duration = self::getLongFixedDuration($order);
                return $startDate + ($duration * 86400);

            default:
                return $startDate;
        }
    }

    /**
     * Получает длительность LONG_FIXED заказа
     *
     * @param \app\models\Order $order
     * @return int
     */
    private static function getLongFixedDuration($order)
    {
        // Реализуйте логику получения длительности
        // Например, из summary или связанных данных
        return 3; // По умолчанию 3 дня
    }
}
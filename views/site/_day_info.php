<?php
// @app/views/calendar/_day_info.php

use app\models\Coworker;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Hours;
use app\models\Order;
use yii\web\View;

/**
 * @var $this View
 * @var $date string
 * @var $employee Coworker
 */

$date = Yii::$app->formatter->asDate($date, 'php:Y-m-d');
$hours = Hours::find()
    ->where(['user_id' => $employee->id, 'date' => $date])
    ->all();

$orders = Order::find()
    ->joinWith('coworkers')
    ->where(['coworker.id' => $employee->id])
    ->andWhere(['date' => strtotime($date)])
    ->all();
?>

    <div class="day-info">
    <h4><?= Html::encode($employee->getName()) ?></h4>
    <p>Дата: <?= Yii::$app->formatter->asDate($date, 'php:d.m.Y') ?></p>

<?php if (!empty($hours)): ?>
    <h5>Часы работы:</h5>
    <ul>
        <?php foreach ($hours as $hour): ?>
            <li>
                <?= $hour->count ?> часов
                <?php if ($hour->start_time && $hour->stop_time): ?>
                    (<?= date('H:i', strtotime($hour->start_time)) ?> - <?= date('H:i', strtotime($hour->stop_time)) ?>)
                <?php endif; ?>
                <?php if ($hour->order_id): ?>
                    - Заказ #<?= $hour->order_id ?>
                <?php endif; ?>
                (<?= $hour->is_payed ? 'Оплачено' : 'Не оплачено' ?>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (!empty($orders)): ?>
    <h5>Заказы:</h5>
    <ul>
        <?php foreach ($orders as $order): ?>
            <li>
                <a href="<?= Url::to(['order/view', 'id' => $order->id]) ?>">
                    Заказ #<?= $order->id ?>
                </a>
                - <?= $order->getStatusTitle() ?>
                <?php if ($order->comment): ?>
                    <br><small><?= Html::encode($order->comment) ?></small>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if (empty($hours) && empty($orders)): ?>
    <p>Нет информации на этот день.</p>
<?php endif; ?>
    </div>
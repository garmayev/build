<?php
// Создаем отдельный partial для содержимого заказа в тултипе
// @app/views/site/_order_timeline_content.php
?>

<!-- Содержимое partial файла _order_timeline_content.php -->
<?php
// @app/views/site/_order_timeline_content.php

/** @var \app\models\Order $order */
/** @var \app\models\Hours $hours */
/** @var \app\models\User $employee */
?>

<div class="order-timeline-content">
    <div class="order-id">Заказ #<?= $order->id ?></div>
    <div class="order-status"><?= $order->getStatusTitle() ?></div>
    <div class="order-mode"><?= $order->getModes()[$order->mode] ?? 'Неизвестно' ?></div>
    <?php if ($hours): ?>
        <div class="order-hours">
            Часы: <?= $hours->count ?> ч.
            <?php if ($hours->start_time && $hours->stop_time): ?>
                <br><?= date('H:i', strtotime($hours->start_time)) ?> - <?= date('H:i', strtotime($hours->stop_time)) ?>
            <?php endif; ?>
        </div>
        <div class="order-payment <?= $hours->is_payed ? 'payed' : 'unpayed' ?>">
            <?= $hours->is_payed ? 'Оплачено' : 'Не оплачено' ?>
        </div>
    <?php endif; ?>
    <?php if ($order->building): ?>
        <div class="order-building">
            Объект: <?= Html::encode($order->building->name) ?>
        </div>
    <?php endif; ?>
    <?php if ($order->comment): ?>
        <div class="order-comment" title="<?= Html::encode($order->comment) ?>">
            <?= mb_substr($order->comment, 0, 50) . (mb_strlen($order->comment) > 50 ? '...' : '') ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .order-timeline-content {
        font-size: 11px;
        line-height: 1.3;
    }

    .order-timeline-content .order-id {
        font-weight: bold;
        color: #007bff;
        margin-bottom: 3px;
    }

    .order-timeline-content .order-status {
        font-size: 10px;
        color: #6c757d;
        margin-bottom: 2px;
    }

    .order-timeline-content .order-mode {
        font-size: 10px;
        font-weight: bold;
        margin-bottom: 3px;
    }

    .order-timeline-content .order-hours {
        color: #28a745;
        font-size: 10px;
        margin-bottom: 2px;
    }

    .order-timeline-content .order-payment {
        font-size: 10px;
        padding: 1px 4px;
        border-radius: 3px;
        display: inline-block;
        margin-bottom: 3px;
    }

    .order-timeline-content .order-payment.payed {
        background: #d4edda;
        color: #155724;
    }

    .order-timeline-content .order-payment.unpayed {
        background: #f8d7da;
        color: #721c24;
    }

    .order-timeline-content .order-building {
        font-size: 10px;
        color: #6c757d;
        margin-bottom: 2px;
    }

    .order-timeline-content .order-comment {
        font-size: 10px;
        color: #6c757d;
        margin-top: 3px;
        font-style: italic;
    }
</style>
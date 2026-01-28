<?php

use yii\web\View;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

/**
 * @var View $this
 * @var int $order_id
 * @var int $coworker_id
 */

echo GridView::widget([
    'dataProvider' => new ActiveDataProvider([
        'query' => \app\models\Hours::find()->where(['order_id' => $order_id, 'user_id' => $coworker_id])
    ]),
    'tableOptions' => [
        'class' => 'table table-striped'
    ],
    'columns' => [
        [
            'headerOptions' => [ 'class' => 'text-center' ],
            'contentOptions' => [ 'class' => 'text-center' ],
            'attribute' => 'date',
            'format' => 'date'
        ],
        [
            'headerOptions' => [ 'class' => 'text-center' ],
            'contentOptions' => [ 'class' => 'text-center' ],
            'attribute' => 'start_time',
            'format' => 'raw',
            'label' => \Yii::t('app', 'Start Time'),
            'value' => function (\app\models\Hours $model) {
                return date('H:i', strtotime($model->start_time));
            }
        ],
        [
            'headerOptions' => [ 'class' => 'text-center' ],
            'contentOptions' => [ 'class' => 'text-center' ],
            'attribute' => 'stop_time',
            'format' => 'raw',
            'label' => \Yii::t('app', 'Stop Time'),
            'value' => function (\app\models\Hours $model) {
                if ($model->stop_time) {
                    return date('H:i', strtotime($model->stop_time));
                }
                return null;
            }
        ],
        [
            'headerOptions' => [ 'class' => 'text-center' ],
            'contentOptions' => [ 'class' => 'text-center' ],
            'attribute' => 'count',
            'label' => \Yii::t('app', 'Count'),
        ],
        [
            'headerOptions' => [ 'class' => 'text-center' ],
            'contentOptions' => [ 'class' => 'text-center' ],
            'attribute' => 'price',
            'format' => 'currency',
            'label' => \Yii::t('app', 'Price'),
        ],
        [
            'headerOptions' => [ 'class' => 'text-center' ],
            'contentOptions' => [ 'class' => 'text-center' ],
            'attribute' => 'amount',
            'format' => 'currency',
            'label' => \Yii::t('app', 'Amount'),
            'value' => function (\app\models\Hours $model) {
                return $model->price * $model->count;
            }
        ],
        [
            'headerOptions' => [ 'class' => 'text-center' ],
            'contentOptions' => [ 'class' => 'text-center' ],
            'attribute' => 'is_payed',
            'format' => 'raw',
            'label' => \Yii::t('app', 'Is payed'),
            'value' => function (\app\models\Hours $model) use ($coworker_id, $order_id) {
                if ($model->stop_time) {
                    $isChecked = $model->is_payed ? "checked=''" : "";
                    return "<div class='form-check form-switch mx-5'>
<input class='form-check-input hour-payed-switch' type='checkbox' {$isChecked} role='switch' data-user_id='{$coworker_id}' data-order_id='{$order_id}' data-date='{$model->date}'>
</div>";
                }
                return "";
            }
        ]
    ],
    'layout' => '{items}'
]);

$this->registerJs(<<<JS
JS
);
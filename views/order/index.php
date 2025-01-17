<?php

use app\models\Order;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;
use yii\grid\GridView;


/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 */

$this->title = \Yii::t('app', 'Orders');

$this->params['breadcrumbs'][] = $this->title;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => false,
    'tableOptions' => [
        'class' => 'table table-striped'
    ],
    'columns' => [
        [
            'attribute' => 'id',
            'label' => '#',
            'value' => function (Order $model) {
                return "#{$model->id}";
            }
        ],
        [
            'attribute' => 'status',
            'value' => function (Order $model) {
                return $model->statusTitle;
            }
        ], [
            'attribute' => 'building_id',
            'label' => \Yii::t('app', 'Building'),
            'value' => function (Order $model) {
                return $model->building->title;
            }
        ],
        'date:date',
        [
            'attribute' => 'type',
            'format' => 'raw',
            'value' => function (Order $model) {
                switch ($model->type) {
                    case Order::TYPE_COWORKER:
                        return \Yii::t('app', 'Coworker');
                    case Order::TYPE_MATERIAL:
                        return \Yii::t('app', 'Material');
                    case Order::TYPE_TECHNIQUE:
                        return \Yii::t('app', 'Technique');
                    default:
                        return \yii\helpers\Html::tag('span', \Yii::t('yii', 'not-set'), ['class' => 'not-set']);
                }
            }
        ], [
            'class' => \yii\grid\ActionColumn::class,
            'buttons' => [
                'view' => function ($url, $model, $key) {
                    return Html::a(Html::tag('i', '', ['class' => 'fas fa-eye']),$url);
                },
                'update' => function ($url, $model, $key) {
                    switch ($model->type) {
                        case Order::TYPE_COWORKER:
                            return Html::a(Html::tag('i', '', ['class' => 'fa-solid fa-pencil']), ['coworker', 'id' => $model->id]);
                        case Order::TYPE_MATERIAL:
                            return Html::a(Html::tag('i', '', ['class' => 'fas fa-pencil']), ['material', 'id' => $model->id]);
                        case Order::TYPE_TECHNIQUE:
                            return Html::a(Html::tag('i', '', ['class' => 'fas fa-pencil']), ['technique', 'id' => $model->id]);
                    }
                },
                'delete' => function ($url, $model, $key) {
                    return Html::a(Html::tag('i', '', ['class' => 'fas fa-trash']),$url);
                },
            ]
        ]
    ]
]);

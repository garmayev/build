<?php

use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\web\View;

/**
 * @var $this View
 * @var $dataProvider ActiveDataProvider
 */

$this->title = \Yii::t('app', 'Intervals');
$this->params['breadcrumbs'][] = $this->title;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => [
        'class' => 'table table-striped table-hover'
    ],
    'summary' => false,
    'columns' => [
        [
            'attribute' => 'id',
            'headerOptions' => ['class' => 'text-center col-md-1 col-3 hide-on-mobile'],
            'filterOptions' => ['class' => 'text-center col-md-1 col-3 hide-on-mobile'],
            'contentOptions' => ['class' => 'text-center col-md-1 col-3 hide-on-mobile'],
        ],
        [
            'attribute' => 'label',
            'headerOptions' => ['class' => 'text-center col-md-7 col-6'],
            'filterOptions' => ['class' => 'text-center col-md-7 col-6'],
            'contentOptions' => ['class' => 'text-center col-md-7 col-6'],
            'value' => function ($model) {
                return $model->label;
            }
        ],
        [
            'attribute' => 'value',
            'headerOptions' => ['class' => 'text-center col-md-3 col-3'],
            'filterOptions' => ['class' => 'text-center col-md-3 col-3'],
            'contentOptions' => ['class' => 'text-center col-md-3 col-3'],
        ],
        [
            'headerOptions' => ['class' => 'text-center col-md-1 col-3'],
            'filterOptions' => ['class' => 'text-center col-md-1 col-3'],
            'contentOptions' => ['class' => 'text-center col-md-1 col-3'],
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
        ]
    ]
]);
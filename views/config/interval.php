<?php

use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\web\View;

/**
 * @var $this View
 * @var $dataProvider ActiveDataProvider
 */

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => [
        'class' => 'table table-striped table-hover'
    ],
    'summary' => false,
    'columns' => [
        'id',
        [
            'attribute' => 'label',
            'value' => function ($model) {
                return \Yii::t('app', $model->label);
            }
        ],
        'value',
        [
            'headerOptions' => ['class' => 'col-1'],
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
        ]
    ]
]);
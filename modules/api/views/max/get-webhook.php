<?php

use \yii\grid\GridView;
use \yii\data\ArrayDataProvider;
use \yii\helpers\Html;

/**
 * @var $this \yii\web\View
**/

$hooks = \Yii::$app->max->send('GET', 'subscriptions');

echo Html::a(\Yii::t('app', 'Add webhook'), ['add-webhook'], ['class' => 'btn btn-success']);

echo GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => $hooks->subscriptions
    ]),
    'tableOptions' => [
        'class' => 'table table-striped table-hover'
    ],
    'summary' => false,
    'columns' => [
        'url',
        'time:datetime',
        [
            'class' => \yii\grid\ActionColumn::class,
            'template' => '{delete}',
            'headerOptions' => [
                'class' => 'col-1'
            ]
        ]
    ]
]);
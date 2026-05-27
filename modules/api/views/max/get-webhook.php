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
            'attribute' => 'update_types',
            'value' => function ($model) {
                return implode(', ', $model['update_types']);
            }
        ],
        [
            'class' => \yii\grid\ActionColumn::class,
            'template' => '{delete}',
            'urlCreator' => function ($action, $model, $key, $index) {
//                \Yii::error($model);
                if ($action === 'delete') {
                    return \yii\helpers\Url::to(['delete', 'url' => $model['url']]);
                }
                return $action;
            },
            'headerOptions' => [
                'class' => 'col-1'
            ]
        ]
    ]
]);
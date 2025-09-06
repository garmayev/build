<?php

use app\models\Order;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;

/**
 * @var View $this
 * @var Order $model
 */

$this->title = \Yii::t('app', 'View Order: {name}', ['name' => "#{$model->id}"]);

$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Orders'), 'url' => ['/order/index']];
$this->params['breadcrumbs'][] = $this->title;

echo Html::a(\Yii::t('app', 'Notify'), ["order/resend-notify", "id" => $model->id], ["class" => "btn btn-success mb-3"]);

echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'building.title',
        [
            'attribute' => 'building.location.address',
            'format' => 'html',
            'value' => function (Order $model) {
                return $model->building->location->link;
            }
        ],
        [
            'attribute' => 'date',
            'value' => function ($model) {
                return Yii::$app->formatter->asDate($model->date, 'php:d m Y');
            }
        ],
        [
            'attribute' => 'status',
            'value' => function (Order $model) {
                return $model->statusTitle;
            }
        ],
        'typeName',
        'comment',
        [
            'attribute' => 'attachments',
            'label' => \Yii::t('app', 'Attachments'),
            'format' => 'raw',
            'value' => function (Order $model) {
                $result = "";
                foreach ($model->attachments as $attachment) {
                    $result .= Html::tag('p', Html::a($attachment->url, $attachment->url, ['target' => '_blank']));
                }
                return $result;
            }
        ],
    ],
    'options' => [
        'class' => 'table table-striped'
    ]
]);

echo GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => $model->requirements
    ]),
    'summary' => false,
    'columns' => [
        [
            'attribute' => 'category.title',
            'headerOptions' => ['class' => 'col-2'],
        ],
        [
            'attribute' => 'count',
            'headerOptions' => ['class' => 'col-2'],
        ],
        [
            'attribute' => 'property.title',
            'headerOptions' => ['class' => 'col-2'],
        ],
        [
            'attribute' => 'type',
            'headerOptions' => ['class' => 'col-2'],
            'value' => function (\app\models\Requirement $model) {
                return \Yii::t('app', $model->type);
            }
        ],
        [
            'attribute' => 'value',
            'headerOptions' => ['class' => 'col-2'],
        ],
        [
            'attribute' => 'dimension.title',
            'headerOptions' => ['class' => 'col-2'],
        ]
    ],
    'tableOptions' => [
        'class' => 'table table-striped'
    ]
]);

echo GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => $model->coworkers
    ]),
    'summary' => false,
    'columns' => [
        [
            'attribute' => 'name',
            'label' => \Yii::t('app', 'Coworkers'),
            'value' => function (\app\models\User $model) {
                return "{$model->profile->family} {$model->profile->name} {$model->profile->surname}";
            }
        ], [
            'attribute' => 'coworkerProperties',
            'label' => \Yii::t('app', 'Properties'),
            'format' => 'raw',
            'value' => function (app\models\User $model) {
                $result = "";
                foreach ($model->userProperties as $userProperty) {
//                        $type = \Yii::t('app', $userProperty->type);
                    $result .= Html::tag("p", "{$userProperty->property->title} {$userProperty->value} {$userProperty->dimension->title}");
                }
                return $result;
            }
        ],
    ],
    'tableOptions' => [
        'class' => 'table table-striped'
    ]
]);
